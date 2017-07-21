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


use OC\Group\Backend;
use OCA\User_LDAP\ILDAPGroupPlugin;
use OCA\User_LDAP\LDAPProvider;
use OCP\IUserSession;


class LDAPGroupManager implements ILDAPGroupPlugin {


	private $ldapProvider;
	private $userSession;

	public function __construct(IUserSession $userSession) {
		$this->userSession = $userSession;
	}

	/**
	 * Check if plugin implements actions
	 *
	 * @param int $actions bitwise-or'ed actions
	 * @return boolean
	 *
	 * Returns the supported actions as int to be
	 * compared with OC_GROUP_BACKEND_CREATE_GROUP etc.
	 */
	public function respondToActions() {
		return Backend::CREATE_GROUP |
			Backend::DELETE_GROUP |
			Backend::ADD_TO_GROUP |
			Backend::REMOVE_FROM_GROUP;
	}

	/**
	 * @param string $gid
	 * @return \OCP\IGroup
	 */
	public function createGroup($gid) {
		$currentUser = $this->userSession->getUser();

		// If the NC user is an LDAP user, s/he will be allowed to create new groups in the corresponding LDAP database
		$currentUserID = $currentUser->getUID();

		$provider = $this->getLDAPProvider();

		$newGroupEntry = $this->buildNewEntry($gid);

		try {
			$connection = $provider->getLDAPConnection($currentUserID);
		} catch (\Exception $exception) {
			if ($exception->getMessage() == "User id not found in LDAP") {
				throw new \Exception("You cannot add a new LDAP Group because you are not a LDAP User.");
			}
			throw $exception;
		}
		$newGroupDN = "cn=$gid,".$provider->getLDAPBaseUsers($currentUserID);

		if ($ret = ldap_add($connection, $newGroupDN, $newGroupEntry)) {
			$provider->clearCache($currentUserID);
			$message = "Create LDAP group '$gid' ($newGroupDN)";
			\OC::$server->getLogger()->notice($message, array('app' => 'ldapusermanagement'));
		} else {
			$message = "Unable to create LDAP group '$gid' ($newGroupDN)";
			\OC::$server->getLogger()->error($message, array('app' => 'ldapusermanagement'));
		}
		return $ret;
	}

	/**
	 * delete a group
	 *
	 * @param string $gid gid of the group to delete
	 * @return bool
	 */
	public function deleteGroup($gid) {
		$currentUser = $this->userSession->getUser();

		// If the NC user is an LDAP user, s/he will be allowed to create new groups in the corresponding LDAP database
		$currentUserID = $currentUser->getUID();

		$provider = $this->getLDAPProvider();

		try {
			$connection = $provider->getLDAPConnection($currentUserID);
		} catch (\Exception $exception) {
			if ($exception->getMessage() == "User id not found in LDAP") {
				throw new \Exception("You cannot add a new LDAP Group because you are not a LDAP User.");
			}
			throw $exception;
		}

		$groupDN = $provider->getGroupDN($gid);

		if ( !ldap_delete($connection, $groupDN) ) {
			$message = "Unable to delete LDAP Group: " . $gid ;
			\OC::$server->getLogger()->error($message, array('app' => 'ldapusermanagement'));
		} else {
			$message = "Delete LDAP Group: " . $gid ;
			\OC::$server->getLogger()->notice($message, array('app' => 'ldapusermanagement'));
		}

	}

	/**
	 * Add a LDAP user to a LDAP group
	 *
	 * @param string $uid Name of the user to add to group
	 * @param string $gid Name of the group in which add the user
	 * @return bool
	 *
	 * Adds a LDAP user to a LDAP group.
	 */
	public function addToGroup($uid, $gid) {
		$currentUser = $this->userSession->getUser();

		// If the NC user is an LDAP user, s/he will be allowed to create new groups in the corresponding LDAP database
		$currentUserID = $currentUser->getUID();

		$provider = $this->getLDAPProvider();

		try {
			$connection = $provider->getLDAPConnection($currentUserID);
		} catch (\Exception $exception) {
			if ($exception->getMessage() == "User id not found in LDAP") {
				throw new \Exception("You cannot add an user to a LDAP Group because you are not a LDAP User.");
			}
			throw $exception;
		}

		$groupDN = $provider->getGroupDN($gid);

		switch (strtolower($connection->ldapGroupMemberAssocAttr)) {

		}
		$entry['memberuid'] = $uid;

		if (!ldap_mod_add ( $connection , $groupDN , $entry)) {
			$message = "Unable to add user " . $uid. " to group " . $gid;
			\OC::$server->getLogger()->error($message, array('app' => 'ldapusermanagement'));
		} else {
			$message = "Add user: " . $uid. " to group: " . $gid;
			\OC::$server->getLogger()->notice($message, array('app' => 'ldapusermanagement'));
		}
	}

	/**
	 * Removes a LDAP user from a LDAP group
	 *
	 * @param string $uid Name of the user to remove from group
	 * @param string $gid Name of the group from which remove the user
	 * @return bool
	 *
	 * removes the user from a group.
	 */
	public function removeFromGroup($uid, $gid) {
		$currentUser = $this->userSession->getUser();

		// If the NC user is an LDAP user, s/he will be allowed to create new groups in the corresponding LDAP database
		$currentUserID = $currentUser->getUID();

		$provider = $this->getLDAPProvider();

		try {
			$connection = $provider->getLDAPConnection($currentUserID);
		} catch (\Exception $exception) {
			if ($exception->getMessage() == "User id not found in LDAP") {
				throw new \Exception("You cannot remove an user from a LDAP Group because you are not a LDAP User.");
			}
			throw $exception;
		}

		$groupDN = $provider->getGroupDN($gid);

		$entry['memberuid'] = $uid;

		if ( !ldap_mod_del ( $connection , $groupDN , $entry) ) {
			$message = "Unable to remove user: " . $uid. " from group: " . $gid;
			\OC::$server->getLogger()->error($message, array('app' => 'ldapusermanagement'));
		} else {
			$message = "Remove user: " . $uid. " from group: " . $gid;
			\OC::$server->getLogger()->notice($message, array('app' => 'ldapusermanagement'));
		}
	}

	public function countUsersInGroup($gid, $search = '') {
		return false;
	}

	public function getGroupDetails($gid) {
		return false;
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

	private function buildNewEntry($gid) {
		return array(
			'objectClass' => array( 'posixGroup' , 'top' ),
			'cn' => $gid,
			'gidnumber' => 5000, // autoincrement needed?
		);
	}

	public function getUserGroups($uid) {

	}

}
