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
use OCP\IConfig;


class LDAPPlugin implements ILDAPPlugin {
	private $container;

	public function __construct($container) {
		$this->container = $container;
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
		return Backend::SET_DISPLAYNAME;
	}

	/**
	 * Create a new user in LDAP Backend
	 *
	 * @param string $uid The username of the user to create
	 * @param string $password The password of the new user
	 * @return bool
	 *
	 */
	public function createUser($username, $password, IUserLDAP $userLDAP) {
		// TODO: Implement createUser() method.
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
	public function setPassword($uid, $password, IUserLDAP $userLDAP) {
		// TODO: Implement setPassword() method.
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
	public function checkPassword($uid, $password, IUserLDAP $userLDAP) {
		$i = 1;
		// TODO: Implement checkPassword() method.
	}

	/**
	 * get the user's home directory
	 *
	 * @param string $uid the username
	 * @return boolean
	 */
	public function getHome($uid, IUserLDAP $userLDAP) {
		// TODO: Implement getHome() method.
	}

	/**
	 * get display name of the user
	 *
	 * @param string $uid user ID of the user
	 * @return string display name
	 */
	public function getDisplayName($uid, IUserLDAP $userLDAP) {
		// TODO: Implement getDisplayName() method.
	}

	/**
	 * set display name of the user
	 *
	 * @param string $uid user ID of the user
	 * @param string $displayName new user's display name
	 * @return bool
	 */
	public function setDisplayName($uid, $displayName, $access) {
		/** @var LDAPProvider $provider */
		$provider = $this->container->query('LDAPProvider');

		$userDN = $provider->getUserDN($uid);

		$connection = $provider->getLDAPConnection($uid);

		$displayNameField = $provider->getLDAPUserDisplayName($uid);

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
	public function canChangeAvatar($uid, IUserLDAP $userLDAP) {
		// TODO: Implement canChangeAvatar() method.
	}

	/**
	 * Count the number of users
	 *
	 * @return int|bool
	 */
	public function countUsers(IUserLDAP $userLDAP) {
		// TODO: Implement countUsers() method.
	}
}