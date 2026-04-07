<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2019-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2017-2019 Cooperativa EITA <eita.org.br>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\LdapWriteSupport;

use Exception;
use LDAP\Connection;
use OC\ServerNotAvailableException;
use OC\User\Backend;
use OCA\LdapWriteSupport\AppInfo\Application;
use OCA\LdapWriteSupport\Service\Configuration;
use OCA\User_LDAP\Exceptions\ConstraintViolationException;
use OCA\User_LDAP\ILDAPUserPlugin;
use OCA\User_LDAP\IUserLDAP;
use OCP\HintException;
use OCP\IImage;
use OCP\IL10N;
use OCP\IUser;
use OCP\IUserManager;
use OCP\IUserSession;
use OCP\LDAP\IDeletionFlagSupport;
use OCP\LDAP\ILDAPProvider;
use Psr\Log\LoggerInterface;

class LDAPUserManager implements ILDAPUserPlugin {
	public function __construct(
		private IUserManager $userManager,
		private IUserSession $userSession,
		private LDAPConnect $ldapConnect,
		private ILDAPProvider $ldapProvider,
		private Configuration $configuration,
		private IL10N $l10n,
		private LoggerInterface $logger,
	) {
		$this->userManager->listen('\OC\User', 'changeUser', [$this, 'changeUserHook']);
		$this->makeLdapBackendFirst();
	}

	/**
	 * Returns the supported actions as int to be
	 * compared with OC_USER_BACKEND_CREATE_USER etc.
	 *
	 * @return int bitwise-or'ed actions
	 */
	#[\Override]
	public function respondToActions(): int {
		$setPassword = $this->canSetPassword() && !$this->ldapConnect->hasPasswordPolicy()
			? Backend::SET_PASSWORD
			: 0;

		return Backend::SET_DISPLAYNAME
			| Backend::PROVIDE_AVATAR
			| Backend::CREATE_USER
			| $setPassword;
	}

	/**
	 *
	 * @param string $uid user ID of the user
	 * @param string $displayName new user's display name
	 * @throws HintException
	 * @throws ServerNotAvailableException
	 */
	#[\Override]
	public function setDisplayName($uid, $displayName): string {
		$userDN = $this->getUserDN($uid);

		$connection = $this->ldapProvider->getLDAPConnection($uid);

		try {
			$displayNameField = $this->ldapProvider->getLDAPDisplayNameField($uid);
			// The LDAP backend supports a second display name field, but it is
			// not exposed at this time. So it is just ignored for now.
		} catch (Exception $e) {
			throw new HintException(
				'Corresponding LDAP User not found',
				$this->l10n->t('Could not find related LDAP entry')
			);
		}

		if (!is_object($connection)) {
			$this->logger->debug('LDAP resource not available', ['app' => 'ldap_write_support']);
			throw new ServerNotAvailableException('LDAP server is not available');
		}
		try {
			if (ldap_mod_replace($connection, $userDN, [$displayNameField => $displayName])) {
				return $displayName;
			}
			throw new HintException('Failed to set display name');
		} catch (ConstraintViolationException $e) {
			throw new HintException(
				$e->getMessage(),
				$this->l10n->t('DisplayName change rejected'),
				$e->getCode()
			);
		}
	}

	/**
	 * checks whether the user is allowed to change his avatar in Nextcloud
	 *
	 * @param string $uid the Nextcloud user name
	 */
	#[\Override]
	public function canChangeAvatar($uid): bool {
		return $this->configuration->hasAvatarPermission();
	}

	/**
	 * Saves NC user avatar to LDAP
	 *
	 * @param IUser $user
	 */
	public function changeAvatar($user): void {
		try {
			$userDN = $this->getUserDN($user->getUID());
		} catch (Exception) {
			return;
		}

		/** @var IImage $avatar */
		$avatar = $user->getAvatarImage(-1);
		if ($avatar) {
			$data = $avatar->data();

			$connection = $this->ldapProvider->getLDAPConnection($user->getUID());
			ldap_mod_replace($connection, $userDN, ['jpegphoto' => $data]);
		}
	}

	/**
	 * Saves NC user email to LDAP
	 *
	 * @param IUser $user
	 * @throws Exception
	 */
	public function changeEmail(IUser $user, string $newEmail): void {
		try {
			$userDN = $this->getUserDN($user->getUID());
		} catch (Exception) {
			return;
		}

		$emailField = $this->ldapProvider->getLDAPEmailField($user->getUID());
		$connection = $this->ldapProvider->getLDAPConnection($user->getUID());
		ldap_mod_replace($connection, $userDN, [$emailField => $newEmail]);
	}

	/**
	 * Create a new user in LDAP Backend
	 *
	 * @param string $uid The username of the user to create
	 * @param string $password The password of the new user
	 * @throws Exception
	 */
	#[\Override]
	public function createUser($uid, $password): string|false {
		$adminUser = $this->userSession->getUser();
		$requireActorFromLDAP = $this->configuration->isLdapActorRequired();
		if ($requireActorFromLDAP && !$adminUser instanceof IUser) {
			throw new Exception('Acting user is not from LDAP');
		}
		try {
			// $adminUser can be null, for example when using the registration app,
			// throw an Exception to fallback on using the global LDAP connection.
			if ($adminUser === null) {
				throw new Exception('No admin user available');
			}
			$connection = $this->ldapProvider->getLDAPConnection($adminUser->getUID());
			// TODO: what about multiple bases?
			$base = $this->ldapProvider->getLDAPBaseUsers($adminUser->getUID());
			$displayNameAttribute = $this->ldapProvider->getLDAPDisplayNameField($adminUser->getUID());
		} catch (Exception $e) {
			if ($requireActorFromLDAP) {
				if ($this->configuration->isPreventFallback()) {
					throw new \Exception('Acting admin is not from LDAP', 0, $e);
				}
				return false;
			}
			$connection = $this->ldapConnect->getLDAPConnection();
			$base = $this->ldapConnect->getLDAPBaseUsers()[0];
			$displayNameAttribute = $this->ldapConnect->getDisplayNameAttribute();
		}

		if ($connection === false) {
			throw new \Exception('Could not bind to LDAP server');
		}

		[$newUserDN, $newUserEntry] = $this->buildNewEntry($uid, $password, $base);

		$newUserDN = $this->ldapProvider->sanitizeDN([$newUserDN])[0];
		$this->ensureAttribute($newUserEntry, $displayNameAttribute, $uid);

		$ret = ldap_add($connection, $newUserDN, $newUserEntry);

		$message = 'Create LDAP user \'{username}\' ({dn})';
		$logMethod = 'info';
		if ($ret === false) {
			$message = 'Unable to create LDAP user \'{username}\' ({dn})';
			$logMethod = 'error';
		}
		$this->logger->$logMethod($message, [
			'app' => Application::APP_ID,
			'username' => $uid,
			'dn' => $newUserDN,
		]);

		if (!$ret && $this->configuration->isPreventFallback()) {
			throw new \Exception('Cannot create user: ' . ldap_error($connection), ldap_errno($connection));
		}

		if ($this->respondToActions() & Backend::SET_PASSWORD) {
			$this->handleSetPassword($newUserDN, $password, $connection);
		}
		return $ret ? $newUserDN : false;
	}

	public function ensureAttribute(array &$ldif, string $attribute, string $fallbackValue): void {
		$lowerCasedLDIF = array_change_key_case($ldif, CASE_LOWER);
		if (!isset($lowerCasedLDIF[strtolower($attribute)])) {
			$ldif[$attribute] = $fallbackValue;
		}
	}

	private function buildNewEntry(string $username, string $password, string $base): array {
		// Make sure the parameters don't fool the following algorithm
		if (str_contains($username, PHP_EOL)) {
			throw new Exception('Username contains a new line');
		}
		if (str_contains($password, PHP_EOL)) {
			throw new Exception('Password contains a new line');
		}
		if (str_contains($base, PHP_EOL)) {
			throw new Exception('Base DN contains a new line');
		}

		$ldif = $this->configuration->getUserTemplate();

		$ldif = str_replace('{UID}', $username, $ldif);
		$ldif = str_replace('{PWD}', $password, $ldif);
		$ldif = str_replace('{BASE}', $base, $ldif);

		$entry = [];
		$lines = explode(PHP_EOL, $ldif);
		foreach ($lines as $line) {
			$split = explode(':', $line, 2);
			$key = trim($split[0]);
			$value = trim($split[1] ?? '');
			if (!isset($entry[$key])) {
				$entry[$key] = $value;
			} elseif (is_array($entry[$key])) {
				$entry[$key][] = $value;
			} else {
				$entry[$key] = [$entry[$key], $value];
			}
		}
		$dn = $entry['dn'];
		unset($entry['dn']);

		return [$dn, $entry];
	}

	public function deleteUser($uid): bool {
		$connection = $this->ldapProvider->getLDAPConnection($uid);
		$userDN = $this->getUserDN($uid);
		$user = $this->userManager->get($uid);
		if ($res = ldap_delete($connection, $userDN)) {
			$message = 'Delete LDAP user (isDeleted): ' . $uid;
			$this->logger->notice($message, ['app' => Application::APP_ID]);
			if (
				$this->ldapProvider instanceof IDeletionFlagSupport
				&& $user instanceof IUser
			) {
				$this->ldapProvider->flagRecord($uid);
			} else {
				$this->logger->warning(
					'Could not run delete process on {uid}',
					['app' => Application::APP_ID, 'uid' => $uid]
				);
			}
		} else {
			$errno = ldap_errno($connection);
			if ($errno === 0x20) { #LDAP_NO_SUCH_OBJECT
				$message = 'Delete LDAP user {uid}: object not found. Is already deleted? Assuming YES';
				$res = true;
			} else {
				$message = 'Unable to delete LDAP user {uid}';
			}
			$this->logger->notice($message, ['app' => Application::APP_ID, 'uid' => $uid]);
		}
		ldap_close($connection);
		return $res;
	}

	/**
	 * checks whether the user is allowed to change their password in Nextcloud
	 *
	 * @return bool either the user can or cannot
	 */
	public function canSetPassword(): bool {
		return $this->configuration->hasPasswordPermission();
	}

	/**
	 * Set password
	 *
	 * @param string $uid The username
	 * @param string $password The new password
	 *
	 * Change the password of a user
	 */
	#[\Override]
	public function setPassword($uid, $password): bool {
		$connection = $this->ldapProvider->getLDAPConnection($uid);
		$userDN = $this->getUserDN($uid);

		return $this->handleSetPassword($userDN, $password, $connection);
	}

	/**
	 * get the user's home directory
	 *
	 * @param string $uid the username
	 */
	#[\Override]
	public function getHome($uid): bool {
		// Not implemented
		return false;
	}

	/**
	 * get display name of the user
	 *
	 * @param string $uid user ID of the user
	 */
	#[\Override]
	public function getDisplayName($uid): string {
		// Not implemented
		return $uid;
	}

	/**
	 * Count the number of users
	 */
	#[\Override]
	public function countUsers(): false {
		// Not implemented
		return false;
	}

	public function makeLdapBackendFirst(): void {
		$backends = $this->userManager->getBackends();
		$otherBackends = [];
		$this->userManager->clearBackends();
		foreach ($backends as $backend) {
			if ($backend instanceof IUserLDAP) {
				$this->userManager->registerBackend($backend);
			} else {
				$otherBackends[] = $backend;
			}
		}

		#insert other backends: database, etc
		foreach ($otherBackends as $backend) {
			$this->userManager->registerBackend($backend);
		}
	}

	/**
	 * @throws Exception
	 */
	public function changeUserHook(IUser $user, string $feature, $attr1, $attr2): void {
		switch ($feature) {
			case 'avatar':
				$this->changeAvatar($user);
				break;
			case 'eMailAddress':
				//attr1 = new email ; attr2 = old email
				$this->changeEmail($user, $attr1);
				break;
		}
	}

	private function getUserDN(string $uid): string {
		return $this->ldapProvider->getUserDN($uid);
	}

	/**
	 * Handle setting user password password
	 *
	 * @param string $userDN The username
	 * @param string $password The new password
	 * @param Connection $connection The LDAP connection to use
	 * @return bool
	 *
	 * Change the password of a user
	 */
	private function handleSetPassword(string $userDN, string $password, Connection $connection): bool {
		try {
			$ret = false;

			// try ldap_exop_passwd first
			if ($this->ldapConnect->hasPasswdExopSupport($connection)) {
				if (ldap_exop_passwd($connection, $userDN, '', $password) === true) {
					// `ldap_exop_passwd` is either FALSE or the password, in the later case return TRUE
					return true;
				}

				$message = 'Failed to set password for user {dn} using ldap_exop_passwd';
				$this->logger->error($message, [
					'ldap_error' => ldap_error($connection),
					'app' => Application::APP_ID,
					'dn' => $userDN,
				]);
			} else {
				// Use ldap_mod_replace in case the server does not support exop_passwd
				$entry = [];
				if ($this->configuration->useUnicodePassword()) {
					$entry['unicodePwd'] = iconv('UTF-8', 'UTF-16LE', '"' . $password . '"');
				} else {
					$entry['userPassword'] = $password;
				}

				if (ldap_mod_replace($connection, $userDN, $entry)) {
					return true;
				}

				$message = 'Failed to set password for user {dn} using ldap_mod_replace';
				$this->logger->error($message, [
					'ldap_error' => ldap_error($connection),
					'app' => Application::APP_ID,
					'dn' => $userDN,
				]);
			}
			return false;
		} catch (\Exception $e) {
			$this->logger->error('Exception occured while setting the password of user {dn}', [
				'app' => Application::APP_ID,
				'exception' => $e,
				'dn' => $userDN,
			]);
			return false;
		}
	}
}
