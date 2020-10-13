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

use Exception;
use OC\Group\Backend;
use OCA\LdapWriteSupport\AppInfo\Application;
use OCA\User_LDAP\Group_Proxy;
use OCA\User_LDAP\ILDAPGroupPlugin;
use OCA\User_LDAP\LDAPProvider;
use OCP\AppFramework\QueryException;
use OCP\IGroupManager;
use OCP\IUserSession;
use OCP\ILogger;
use OCP\LDAP\ILDAPProvider;

class LDAPGroupManager implements ILDAPGroupPlugin {

	/** @var ILDAPProvider */
	private $ldapProvider;

	/** @var IUserSession */
	private $userSession;

	/** @var IGroupManager */
	private $groupManager;

	/** @var LDAPConnect */
	private $ldapConnect;
	/** @var ILogger */
	private $logger;

	public function __construct(IGroupManager $groupManager, IUserSession $userSession, LDAPConnect $ldapConnect, ILogger $logger, ILDAPProvider $ldapProvider) {
		$this->groupManager = $groupManager;
		$this->userSession = $userSession;
		$this->ldapConnect = $ldapConnect;
		$this->logger = $logger;
		$this->ldapProvider = $ldapProvider;

		if($this->ldapConnect->groupsEnabled()) {
			$this->makeLdapBackendFirst();
		}
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
		if(!$this->ldapConnect->groupsEnabled()) {
			return 0;
		}
		return Backend::CREATE_GROUP |
			Backend::DELETE_GROUP |
			Backend::ADD_TO_GROUP |
			Backend::REMOVE_FROM_GROUP;
	}

	/**
	 * @param string $gid
	 * @return string|null
	 */
	public function createGroup($gid) {
		$adminUser = $this->userSession->getUser();
		$requireActorFromLDAP = $this->configuration->isLdapActorRequired();
		if ($requireActorFromLDAP && !$adminUser instanceof IUser) {
			throw new Exception('Acting user is not from LDAP');
		}
		try {
			$connection = $this->ldapProvider->getLDAPConnection($adminUser->getUID());
			// TODO: what about multiple bases?
			$base = $this->ldapProvider->getLDAPBaseGroups($adminUser->getUID());
		} catch (Exception $e) {
			if ($requireActorFromLDAP) {
				if ($this->configuration->isPreventFallback()) {
					throw new \Exception('Acting admin is not from LDAP', 0, $e);
				}
				return false;
			}
			$connection = $this->ldapConnect->getLDAPConnection();
			$base = $this->ldapConnect->getLDAPBaseGroups()[0];
		}

        list($newGroupDN, $newGroupEntry) = $this->buildNewEntry($gid, $base);
		$newGroupDN = $this->ldapProvider->sanitizeDN([$newGroupDN])[0];

		if ($ret = ldap_add($connection, $newGroupDN, $newGroupEntry)) {
			$message = "Create LDAP group '$gid' ($newGroupDN)";
			$this->logger->notice($message, ['app' => Application::APP_ID]);
		} else {
			$message = "Unable to create LDAP group '$gid' ($newGroupDN)";
			$this->logger->error($message, ['app' => Application::APP_ID]);
		}
		return $ret ? $newGroupDN : null;
	}

	/**
	 * delete a group
	 *
	 * @param string $gid gid of the group to delete
	 * @return bool
	 * @throws Exception
	 */
	public function deleteGroup($gid) {
		$connection = $this->ldapProvider->getGroupLDAPConnection($gid);
		$groupDN = $this->ldapProvider->getGroupDN($gid);

		if (!$ret = ldap_delete($connection, $groupDN)) {
			$message = "Unable to delete LDAP Group: " . $gid;
			$this->logger->error($message, ['app' => Application::APP_ID]);
		} else {
			$message = "Delete LDAP Group: " . $gid;
			$this->logger->notice($message, ['app' => Application::APP_ID]);
		}
		return $ret;
	}

	/**
	 * Add a LDAP user to a LDAP group
	 *
	 * @param string $uid Name of the user to add to group
	 * @param string $gid Name of the group in which add the user
	 * @return bool
	 *
	 * Adds a LDAP user to a LDAP group.
	 * @throws Exception
	 */
	public function addToGroup($uid, $gid) {
		$connection = $this->ldapProvider->getGroupLDAPConnection($gid);
		$groupDN = $this->ldapProvider->getGroupDN($gid);

		$entry = [];
		switch ($this->ldapProvider->getLDAPGroupMemberAssoc($gid)) {
			case 'memberUid':
				$entry['memberuid'] = $uid;
				break;
			case 'uniqueMember':
				$entry['uniquemember'] = $this->ldapProvider->getUserDN($uid);
				break;
			case 'member':
				$entry['member'] = $this->ldapProvider->getUserDN($uid);
				break;
			case 'gidNumber':
				throw new Exception('Cannot add to group when gidNumber is used as relation');
				break;
		}

		if (!$ret = ldap_mod_add($connection, $groupDN, $entry)) {
			$message = "Unable to add user " . $uid . " to group " . $gid;
			$this->logger->error($message, ['app' => Application::APP_ID]);
		} else {
			$message = "Add user: " . $uid . " to group: " . $gid;
			$this->logger->notice($message, ['app' => Application::APP_ID]);
		}
		return $ret;
	}

	/**
	 * Removes a LDAP user from a LDAP group
	 *
	 * @param string $uid Name of the user to remove from group
	 * @param string $gid Name of the group from which remove the user
	 * @return bool
	 *
	 * removes the user from a group.
	 * @throws Exception
	 */
	public function removeFromGroup($uid, $gid) {
		$connection = $this->ldapProvider->getGroupLDAPConnection($gid);
		$groupDN = $this->ldapProvider->getGroupDN($gid);

		$entry = [];
		switch ($this->ldapProvider->getLDAPGroupMemberAssoc($gid)) {
			case 'memberUid':
				$entry['memberuid'] = $uid;
				break;
			case 'uniqueMember':
				$entry['uniquemember'] = $this->ldapProvider->getUserDN($uid);
				break;
			case 'member':
				$entry['member'] = $this->ldapProvider->getUserDN($uid);
				break;
			case 'gidNumber':
				throw new Exception('Cannot remove from group when gidNumber is used as relation');
				break;
		}

		if (!$ret = ldap_mod_del($connection, $groupDN, $entry)) {
			$message = "Unable to remove user: " . $uid . " from group: " . $gid;
			$this->logger->error($message, ['app' => Application::APP_ID]);
		} else {
			$message = "Remove user: " . $uid . " from group: " . $gid;
			$this->logger->notice($message, ['app' => Application::APP_ID]);
		}
		return $ret;
	}


	public function countUsersInGroup($gid, $search = '') {
		return false;
	}

	public function getGroupDetails($gid) {
		return false;
	}

	public function isLDAPGroup($gid) {
		try {
			return !empty($this->ldapProvider->getGroupDN($gid));
		} catch (Exception $e) {
			return false;
		}
	}

	private function buildNewEntry($gid, $base) {
        $ldif = $this->configuration->getGroupTemplate();

		$ldif = str_replace('{GID}', $gid, $ldif);
		$ldif = str_replace('{BASE}', $base, $ldif);

		$entry = [];
		$lines = explode(PHP_EOL, $ldif);
		foreach ($lines as $line) {
			$split = explode(':', $line, 2);
			$key = trim($split[0]);
			$value = trim($split[1]);
			if (!isset($entry[$key])) {
				$entry[$key] = $value;
			} else if (is_array($entry[$key])) {
				$entry[$key][] = $value;
			} else {
				$entry[$key] = [$entry[$key], $value];
			}
		}
		$dn = $entry['dn'];
		unset($entry['dn']);

		return [$dn, $entry];
	}

	public function makeLdapBackendFirst() {
		$backends = $this->groupManager->getBackends();
		$otherBackends = [];
		$this->groupManager->clearBackends();
		foreach ($backends as $backend) {
			if ($backend instanceof Group_Proxy) {
				$this->groupManager->addBackend($backend);
			} else {
				$otherBackends[] = $backend;
			}
		}

		#insert other backends: database, etc
		foreach ($otherBackends as $backend) {
			$this->groupManager->addBackend($backend);
		}
	}

}
