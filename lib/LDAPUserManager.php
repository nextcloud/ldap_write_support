<?php
/**
 * @copyright Copyright (c) 2017 EITA Cooperative (eita.org.br)
 *
 * @author Alan Tygel <alan@eita.org.br>
 * @author Vinicius Brand <vinicius@eita.org.br>
 * @author Daniel Tygel <dtygel@eita.org.br>
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
 *
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\LdapWriteSupport;

use OC\HintException;
use OC\ServerNotAvailableException;
use OC\User\Backend;
use OCA\LdapWriteSupport\AppInfo\Application;
use OCA\LdapWriteSupport\Service\Configuration;
use OCA\User_LDAP\Exceptions\ConstraintViolationException;
use OCA\User_LDAP\ILDAPUserPlugin;
use OCA\User_LDAP\IUserLDAP;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IImage;
use OCP\IL10N;
use OCP\ILogger;
use OCP\IUser;
use OCP\IUserManager;
use OCP\IUserSession;
use OCP\LDAP\ILDAPProvider;
use OCP\PreConditionNotMetException;


class LDAPUserManager implements ILDAPUserPlugin {
	/** @var ILDAPProvider */
	private $ldapProvider;

	/** @var IUserSession */
	private $userSession;

	/** @var IGroupManager */
	private $groupManager;

	/** @var IUserManager */
	private $userManager;

	/** @var LDAPConnect */
	private $ldapConnect;

	/** @var IConfig */
	private $ocConfig;
	/** @var Configuration */
	private $configuration;
	/** @var IL10N */
	private $l10n;
	/** @var ILogger */
	private $logger;

	/**
	 * LDAPUserManager constructor.
	 *
	 * @param IUserManager $userManager
	 * @param IGroupManager $groupManager
	 * @param IUserSession $userSession
	 * @param LDAPConnect $ldapConnect
	 * @param IConfig $ocConfig
	 * @param ILDAPProvider $ldapProvider
	 * @param Configuration $configuration
	 * @param IL10N $l10n
	 * @param ILogger $logger
	 */
	public function __construct(IUserManager $userManager, IGroupManager $groupManager, IUserSession $userSession, LDAPConnect $ldapConnect, IConfig $ocConfig, ILDAPProvider $ldapProvider, Configuration $configuration, IL10N $l10n, ILogger $logger) {
		$this->userManager = $userManager;
		$this->groupManager = $groupManager;
		$this->userSession = $userSession;
		$this->ldapConnect = $ldapConnect;
		$this->ocConfig = $ocConfig;

		$this->userManager->listen('\OC\User', 'changeUser', [$this, 'changeUserHook']);

		$this->makeLdapBackendFirst();
		$this->ldapProvider = $ldapProvider;
		$this->configuration = $configuration;
		$this->l10n = $l10n;
		$this->logger = $logger;
	}

	/**
	 * Check if plugin implements actions
	 *
	 * @param int $actions bitwise-or'ed actions
	 * @return boolean
	 *
	 * Returns the supported actions as int to be
	 * compared with OC_USER_BACKEND_CREATE_USER etc.
	 */
	public function respondToActions() {
		return Backend::SET_DISPLAYNAME |
		       Backend::PROVIDE_AVATAR |
			   Backend::CREATE_USER;
	}

	/**
	 *
	 * @param string $uid user ID of the user
	 * @param string $displayName new user's display name
	 * @return string
	 * @throws HintException
	 * @throws ServerNotAvailableException
	 */
	public function setDisplayName($uid, $displayName) {
		$userDN = $this->getUserDN($uid);

		$connection = $this->ldapProvider->getLDAPConnection($uid);

		try {
			$displayNameField = $this->ldapProvider->getLDAPDisplayNameField($uid);
			// The LDAP backend supports a second display name field, but it is
			// not exposed at this time. So it is just ignored for now.
		} catch (\Exception $e) {
			throw new HintException(
				'Corresponding LDAP User not found',
				$this->l10n->t('Could not find related LDAP entry')
			);
		}

		if (!is_resource($connection)) {
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
	 * @return boolean either the user can or cannot
	 */
	public function canChangeAvatar($uid) {
		return true;
	}

	/**
	 * Saves NC user avatar to LDAP
	 *
	 * @param IUser $user
	 */
	public function changeAvatar($user) {
		try {
			$userDN = $this->getUserDN($user->getUID());
		} catch (\Exception $e) {
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
	 */
	public function changeEmail($user, $newEmail) {
		try {
			$userDN = $this->getUserDN($user->getUID());
		} catch (\Exception $e) {
			return;
		}

		$emailField = $this->ldapProvider->getLDAPEmailField($user->getUID());
		$connection = $this->ldapProvider->getLDAPConnection($user->getUID());
		ldap_mod_replace($connection, $userDN, [$emailField => $newEmail]);
	}

	/**
	 * Create a new user in LDAP Backend
	 *
	 * @param string $username The username of the user to create
	 * @param string $password The password of the new user
	 * @return bool|\OCP\IUser the created user of false
	 *
	 */
	public function createUser($username, $password) {
		$requireActorFromLDAP = (bool)$this->ocConfig->getAppValue('ldap_write_support', 'create.requireActorFromLDAP', '1');
		$adminUser = $this->userSession->getUser();
		if($requireActorFromLDAP && !$adminUser instanceof IUser) {
			throw new \Exception('Acting user is not from LDAP');
		}
		try {
			$connection = $this->ldapProvider->getLDAPConnection($adminUser->getUID());
			// TODO: what about multiple bases?
			$base = $this->ldapProvider->getLDAPBaseUsers($adminUser->getUID());
		} catch (\Exception $e) {
			if($requireActorFromLDAP) {
				if((bool)$this->ocConfig->getAppValue('ldap_write_support', 'create.preventLocalFallback', '1')) {
					throw $e;
				}
				return false;
			}
			$connection = $this->ldapConnect->getLDAPConnection();
			$base = $this->ldapConnect->getLDAPBaseUsers();
		}

		list($newUserDN, $newUserEntry) = $this->buildNewEntry($username, $password, $base);

		if ($ret = ldap_add($connection, $newUserDN, $newUserEntry)) {
			$message = "Create LDAP user '$username' ($newUserDN)";
			\OC::$server->getLogger()->notice($message, ['app' => 'ldap_write_support']);
		} else {
			$message = "Unable to create LDAP user '$username' ($newUserDN)";
			\OC::$server->getLogger()->error($message, ['app' => 'ldap_write_support']);
		}
		ldap_close($connection);
		return $ret ? $newUserDN : null;
	}

	public function buildNewEntry($username, $password, $base) {
		$ldif = $this->configuration->getUserTemplate();

		$rndUid = bin2hex(random_bytes(5));
		$ldif = str_replace('{RND_UID}', $rndUid, $ldif);
		$ldif = str_replace('{UID}', $username, $ldif);
		$ldif = str_replace('{PWD}', $password, $ldif);
		$ldif = str_replace('{BASE}', $base, $ldif);

		$entry = [];
		$lines = explode(PHP_EOL, $ldif);
		foreach ($lines as $line) {
			$split = explode(':', $line);
			$key = trim($split[0]);
			$value = trim($split[1]);
			if(!isset($entry[$key])) {
				$entry[$key] = $value;
			} else if(is_array($entry[$key])) {
				$entry[$key][] = $value;
			} else {
				$entry[$key] = [$entry[$key], $value];
			}
		}
		$dn = $entry['dn'];
		unset($entry['dn']);

		return [$dn, $entry];
	}

	/**
	 * @param $uid
	 * @return bool
	 * @throws PreConditionNotMetException
	 */
	public function deleteUser($uid) {
		$connection = $this->ldapProvider->getLDAPConnection($uid);
		$userDN = $this->getUserDN($uid);

		if ($res = ldap_delete($connection, $userDN)) {
			$message = "Delete LDAP user (isDeleted): " . $uid;
			$this->logger->notice($message, ['app' => Application::APP_ID]);

			// set the deletion flag, so the LDAP Backend will be willing to remove the user
			$this->ocConfig->setUserValue($uid, 'user_ldap', 'isDeleted', 1);
			$user = $this->userManager->get($uid);
			if ($user instanceof IUser) {
				$user->delete();
			} else {
				$this->logger->warning(
					'Could not run delete process on {uid}',
					['app' => Application::APP_ID, 'uid' => $uid]
				);
			}
		} else {
			$errno = ldap_errno($connection);
			if ($errno == 0x20) { #LDAP_NO_SUCH_OBJECT
				$message = "Delete LDAP user {uid}: object not found. Is already deleted? Assuming YES";
				$res = true;
			} else {
				$message = "Unable to delete LDAP user {uid}";
			}
			$this->logger->notice($message, ['app' => Application::APP_ID, 'uid' => $uid]);
		}
		ldap_close($connection);
		return $res;
	}

	/**
	 * Set password
	 *
	 * @param string $uid The username
	 * @param string $password The new password
	 * @return bool
	 *
	 * Change the password of a user
	 */
	public function setPassword($uid, $password) {
		// Not implemented
		return false;
	}

	/**
	 * get the user's home directory
	 *
	 * @param string $uid the username
	 * @return boolean
	 */
	public function getHome($uid) {
		// Not implemented
		return false;
	}

	/**
	 * get display name of the user
	 *
	 * @param string $uid user ID of the user
	 * @return string display name
	 */
	public function getDisplayName($uid) {
		// Not implemented
		return false;
	}

	/**
	 * Count the number of users
	 *
	 * @return int|bool
	 */
	public function countUsers() {
		// Not implemented
		return false;
	}

	public function makeLdapBackendFirst() {
		$backends = $this->userManager->getBackends();
		$otherBackends = [];
		$this->userManager->clearBackends();
		foreach ($backends as $backend) {
			if ($backend instanceof IUserLDAP) {
				\OC_User::useBackend($backend);
			} else {
				$otherBackends[] = $backend;
			}
		}

		#insert other backends: database, etc
		foreach ($otherBackends as $backend) {
			\OC_User::useBackend($backend);
		}
	}

	public function changeUserHook($user, $feature, $attr1, $attr2) {
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

	private function getUserDN($uid) {
		return $this->ldapProvider->getUserDN($uid);
	}
}
