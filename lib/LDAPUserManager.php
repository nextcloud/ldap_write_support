<?php
/**
 * @copyright Copyright (c) 2017 EITA Cooperative (eita.org.br)
 *
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
use OCA\User_LDAP\ILDAPPlugin;
use OCA\User_LDAP\IUserLDAP;
use OCA\User_LDAP\LDAPProvider;
use OCP\AppFramework\IAppContainer;
use OCP\IConfig;
use OCP\IImage;
use OCP\IUser;
use OCP\IUserSession;
use Symfony\Component\EventDispatcher\GenericEvent;


class LDAPUserManager implements ILDAPPlugin {

	/** @var  IAppContainer */
	private $container;
	private $provider;

	public function __construct($container) {
		$this->container = $container;

		#hook to listen to LDAP backend addition
		\OCP\Util::connectHook('\OCA\User_LDAP\User\User','postLDAPBackendAdded', $this, "postLDAPBackendAdded");

		$userManager = $this->container->query('UserManager');

		$userManager->listen('\OC\User', 'changeUser', array($this, 'changeUserHook'));

		//$cb5 = ['OCA\Ldapusermanagement\LDAPUserManagerDeprecated', 'changeLDAPUserAttributes'];
		$eventDispatcher = \OC::$server->getEventDispatcher();
		$eventDispatcher->addListener('OC\AccountManager::userUpdated', array($this, 'changeUserAttributesHook'));
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

		$userDN = $provider->getUserDN($uid);

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
		$userDN = $this->getLdapProvider()->getUserDN($user->getUID());
		if ($userDN) {
			/** @var IImage $avatar */
			$avatar = $user->getAvatarImage(-1);
			if ($avatar) {
				$data = $avatar->data();

				$provider = $this->getLDAPProvider();

				$connection = $provider->getLDAPConnection($user->getUID());
				ldap_mod_replace($connection, $userDN, array('jpegphoto' => $data));
			}
		} else {
			// TODO: log that this NC user is not a ldap user
		}
	}

	/**
	 * Saves NC user email to LDAP
	 *
	 * @param IUser $user
	 */
	public function changeEmail($user, $newEmail) {
		$userDN = $this->getLdapProvider()->getUserDN($user->getUID());
		if ($userDN) {
			$provider = $this->getLDAPProvider();
			$emailField = $provider->getLDAPEmailField($user->getUID());
			$connection = $provider->getLDAPConnection($user->getUID());
			ldap_mod_replace($connection, $userDN, array($emailField => $newEmail));
		} else {
			// TODO: log that this NC user is not a ldap user
		}
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

		/** @var IUserSession $session */
		$session = $this->container->query("UserSession");

		$currentUser = $session->getUser();

		// If the NC user is an LDAP user, she will be allowed to create new users in the corresponding LDAP database
		$currentUserID = $currentUser->getUID();

		$provider = $this->getLDAPProvider();

		$newUserEntry = $this->buildNewUserEntry($username, $password);
		try {
			$connection = $provider->getLDAPConnection($currentUserID);
		} catch (\Exception $exception) {
			if ($exception->getMessage() == "User id not found in LDAP") {
				throw new \Exception("You cannot add a new LDAP User because you are not a LDAP User.");
			}
			throw $exception;
		}
		$newUserDN = "cn=$username,".$provider->getLDAPBaseUsers($currentUserID);

		if ($ret = ldap_add($connection, $newUserDN, $newUserEntry)) {
			$message = "Create LDAP user '$username' ($newUserDN)";
			\OC::$server->getLogger()->notice($message, array('app' => 'ldapusermanagement'));
		} else {
			$message = "Unable to create LDAP user '$username' ($newUserDN)";
			\OC::$server->getLogger()->error($message, array('app' => 'ldapusermanagement'));
		}
		return $ret;
	}

	public function buildNewUserEntry($username, $password) {
		$entry = array(
			'o' => $username ,
			'objectClass' => array( 'inetOrgPerson', 'posixAccount', 'top'),
			'cn' => $username ,
			'gidnumber' => 500, // TODO: Why this????
			'homedirectory' => 'x', // ignored by nextcloud
			'mail' => 'x@x.com',
			'sn' => $username ,
			'uid' => $username , // mandatory
			'uidnumber' => 2010, // mandatory // TODO: Why this????
			'userpassword' => $password ,
			'displayName' => $username,
			'street' => "address",
		);
		return $entry;
	}

	public function deleteUser($uid) {
		$provider = $this->getLDAPProvider();

		$connection = $provider->getLDAPConnection($uid);

		$userDN = $provider->getUserDN($uid);

		if ($res = ldap_delete($connection, $userDN)) {
			$message = "Delete LDAP user (isDeleted): " . $user->getUID();
			\OC::$server->getLogger()->notice($message, array('app' => 'ldapusermanagement'));

			\OCP\Config::setUserValue($user->getUID(), 'user_ldap', 'isDeleted', 1);
		} else {
			$message = "Unable to delete LDAP user " . $user->getUID();
			\OC::$server->getLogger()->error($message, array('app' => 'ldapusermanagement'));
		}
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
	 * Check if the password is correct
	 *
	 * @param string $uid The username
	 * @param string $password The password
	 * @return bool
	 *
	 * Check if the password is correct without logging in the user
	 */
	public function checkPassword($uid, $password) {
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

	public function postLDAPBackendAdded() {
		$userManager =  \OC::$server->getUserManager();
		$backends = $userManager->getBackends();
		$userManager->clearBackends();
		for ($i = count($backends)-1; $i >= 0; $i--) {
			\OC_User::useBackend($backends[$i]);
		}
	}

	/**
	 * @param GenericEvent $event
	 */
	public static function changeUserAttributesHook ( GenericEvent $event ){
		return false;

		$user = $event->getSubject();

		$ds = LDAPConnect::bind();
		$dn = "cn=" . $user->getUID() . "," . \OCP\Config::getAppValue('user_ldap','ldap_base_users','');

		$accountManager = new \OC\Accounts\AccountManager (
			\OC::$server->getDatabaseConnection(),
			\OC::$server->getEventDispatcher(),
			\OC::$server->getJobList()
		);

		$userData = $accountManager->getUser( $user );

		$entry = NULL;
		$entry['mail'] = $userData['email']['value'];
		$entry['displayName'] = $userData['displayname']['value'];
		if ($userData['address']['value'])
			$entry['street'] = $userData['address']['value'];

		if (!ldap_mod_replace ( $ds , $dn , $entry)) {
			$message = "Unable to modify user attributes " . $entry['mail'] . " and " . $entry['displayName'] . " and " . $userData['address']['value'];
			\OC::$server->getLogger()->error($message, array('app' => 'ldapusermanagement'));
		} else {
			$message = "Modify user attributes " . $entry['mail'] . " and " . $entry['displayName'] . " and " . $userData['address']['value'];
			\OC::$server->getLogger()->notice($message, array('app' => 'ldapusermanagement'));
		}
	}

	/**
	 * Provides LDAP Provider. Cannot be established in constructor
	 *
	 * @return LDAPProvider
	 */
	private function getLDAPProvider() {
		if (!$this->provider) {
			$this->provider = $this->container->query('LDAPProvider');
		}
		return $this->provider;
	}

	public function changeUserHook($user, $feature, $attr1, $attr2) {
		$i = 1;
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
}