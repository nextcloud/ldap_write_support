<?php
/**
 * @copyright Copyright (c) 2017 EITA Cooperative (eita.org.br)
 *
 * @author Alan Tygel <alan@eita.org.br>
 * @author Vinicius Brand <vinicius@eita.org.br>
 * @author Daniel Tygel <dtygel@eita.org.br>
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

namespace OCA\Ldapusermanagement;


use OC\HintException;
use OC\User\Backend;
use OCA\User_LDAP\Exceptions\ConstraintViolationException;
use OCA\User_LDAP\ILDAPUserPlugin;
use OCA\User_LDAP\IUserLDAP;
use OCA\User_LDAP\LDAPProvider;
use OCP\IConfig;
use OCP\IDBConnection;
use OCP\IGroup;
use OCP\IGroupManager;
use OCP\IImage;
use OCP\IUser;
use OCP\IUserManager;
use OCP\IUserSession;
use OCP\PreConditionNotMetException;


class LDAPUserManager implements ILDAPUserPlugin {

	private $ldapProvider;
	private $userSession;

	/** @var IGroupManager */
	private $groupManager;

	/** @var IUserManager */
	private $userManager;

	/** @var LDAPConnect */
	private $ldapConnect;

	/** @var IConfig */
	private $ocConfig;

	public function __construct(IUserManager $userManager, IGroupManager $groupManager, IUserSession $userSession, LDAPConnect $ldapConnect, IConfig $ocConfig) {
		$this->userManager = $userManager;
		$this->groupManager = $groupManager;
		$this->userSession = $userSession;
		$this->ldapConnect = $ldapConnect;
		$this->ocConfig = $ocConfig;

		$this->userManager->listen('\OC\User', 'changeUser', array($this, 'changeUserHook'));

		$this->makeLdapBackendFirst();
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
	 * set display name of the user
	 *
	 * @param string $uid user ID of the user
	 * @param string $displayName new user's display name
	 * @return bool
	 */
	public function setDisplayName($uid, $displayName) {
		/** @var LDAPProvider $provider */
		$provider = $this->getLDAPProvider();

		$userDN = $this->getUserDN($uid);

		$connection = $provider->getLDAPConnection($uid);

		$displayNameField = $provider->getLDAPDisplayNameField($uid);

		if (!is_resource($connection)) {
			//LDAP not available
			\OCP\Util::writeLog('user_ldap', 'LDAP resource not available.', \OCP\Util::DEBUG);
			return false;
		}
		try {
			return ldap_mod_replace($connection,$userDN, array($displayNameField => $displayName));
		} catch(ConstraintViolationException $e) {
			throw new HintException('DisplayName change rejected.', \OC::$server->getL10N('user_ldap')->t('DisplayName change rejected. Hint: ').$e->getMessage(), $e->getCode());
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

			$provider = $this->getLDAPProvider();

			$connection = $provider->getLDAPConnection($user->getUID());
			ldap_mod_replace($connection, $userDN, array('jpegphoto' => $data));
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

		$provider = $this->getLDAPProvider();
		$emailField = $provider->getLDAPEmailField($user->getUID());
		$connection = $provider->getLDAPConnection($user->getUID());
		ldap_mod_replace($connection, $userDN, array($emailField => $newEmail));
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

		// Check the name for bad characters
		// NOT allowed in user_ldap: uppercase letters or underscore
		if (preg_match('/[A-Z_]/', $username)) {
			$l = \OC::$server->getL10N('user_ldap_extended');
			throw new \InvalidArgumentException($l->t('Uppercase letters and underscore (_) are not allowed in usernames.'));
		}

		# FIXME could not create user using LDAPProvider, because its methods rely
		# on passing an already inserted uid, which we do not have at this point

		$newUserEntry = $this->buildNewEntry($username, $password);
		$connection = $this->ldapConnect->getLDAPConnection();
		$newUserDN = "cn=$username,".$this->ldapConnect->getLDAPBaseUsers();

		if ($ret = ldap_add($connection, $newUserDN, $newUserEntry)) {
			$message = "Create LDAP user '$username' ($newUserDN)";
			\OC::$server->getLogger()->notice($message, array('app' => 'ldapusermanagement'));



		} else {
			$message = "Unable to create LDAP user '$username' ($newUserDN)";
			\OC::$server->getLogger()->error($message, array('app' => 'ldapusermanagement'));
		}
		ldap_close($connection);
		return $ret;
	}

	public function buildNewEntry($username, $password) {
		$entry = array(
			'o' => $username ,
			'objectClass' => array( 'inetOrgPerson', 'posixAccount', 'top'),
			'cn' => $username ,
			'gidnumber' => 1, // FIXME: Why this????
			'homedirectory' => 'x', // ignored by nextcloud
			'mail' => $username . '@rios.org.br',
			'sn' => $username ,
			'uid' => $username , // mandatory
			'uidnumber' => 2010, // mandatory // FIXME: Why this????
			'userpassword' => $password ,
			'displayName' => $username,
			'street' => "address",
		);
		return $entry;
	}

	public function deleteUser($uid) {
		$provider = $this->getLDAPProvider();

		$connection = $provider->getLDAPConnection($uid);

		$userDN = $this->getUserDN($uid);

		//Remove user from all groups before deleting...
		$user = $this->userManager->get($uid);

		/** @var IGroup[] $userGroups */
		$userGroups = $this->groupManager->getUserGroups($user);
		foreach ($userGroups as $userGroup) {
			$userGroup->removeUser($user);
		}

		if ($res = ldap_delete($connection, $userDN)) {
			$message = "Delete LDAP user (isDeleted): " . $uid;
			\OC::$server->getLogger()->notice($message, array('app' => 'ldapusermanagement'));

			$this->ocConfig->setUserValue($uid, 'user_ldap', 'isDeleted', 1);
		} else {
			$message = "Unable to delete LDAP user " . $uid;
			\OC::$server->getLogger()->error($message, array('app' => 'ldapusermanagement'));
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
		$otherBackends = array();
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

	/**
	 * Provides LDAP Provider. Cannot be established in constructor
	 *
	 * @return LDAPProvider
	 */
	private function getLDAPProvider() {
		if (!$this->ldapProvider) {
			$this->ldapProvider = \OC::$server->query('LDAPProvider');
		}
		return $this->ldapProvider;
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
		return $this->getLDAPProvider()->getUserDN($uid);
		//return "cn=$uid,".$this->ldapConnect->getLDAPBaseUsers();
	}
}
