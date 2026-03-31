<?php

/**
 * SPDX-FileCopyrightText: 2019-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2017-2019 Cooperativa EITA <eita.org.br>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\LdapWriteSupport;

use Exception;
use OCA\User_LDAP\Group_Proxy;
use OCA\User_LDAP\ILDAPGroupPlugin;
use OCP\GroupInterface;
use OCP\IGroupManager;
use OCP\LDAP\ILDAPProvider;
use Psr\Log\LoggerInterface;

class LDAPGroupManager implements ILDAPGroupPlugin {
	public function __construct(
		private IGroupManager $groupManager,
		private LDAPConnect $ldapConnect,
		private LoggerInterface $logger,
		private ILDAPProvider $ldapProvider,
	) {
		if ($this->ldapConnect->groupsEnabled()) {
			$this->makeLdapBackendFirst();
		}
	}

	/**
	 * Returns the supported actions as int to be
	 * compared with OC_GROUP_BACKEND_CREATE_GROUP etc.
	 *
	 * @return int bitwise-or'ed actions
	 */
	public function respondToActions(): int {
		if (!$this->ldapConnect->groupsEnabled()) {
			return 0;
		}
		return GroupInterface::CREATE_GROUP |
			GroupInterface::DELETE_GROUP |
			GroupInterface::ADD_TO_GROUP |
			GroupInterface::REMOVE_FROM_GROUP;
	}

	/**
	 * @param string $gid
	 */
	public function createGroup($gid): ?string {
		/**
		 * FIXME could not create group using LDAPProvider, because its methods rely
		 * on passing an already inserted [ug]id, which we do not have at this point.
		 */

		$newGroupEntry = $this->buildNewEntry($gid, $this->ldapConnect->getGroupMemberAssocAttribute());
		$connection = $this->ldapConnect->getLDAPConnection();
		$newGroupDN = "cn=$gid," . $this->ldapConnect->getLDAPBaseGroups()[0];
		$newGroupDN = $this->ldapProvider->sanitizeDN([$newGroupDN])[0];

		if ($connection && ($ret = ldap_add($connection, $newGroupDN, $newGroupEntry))) {
			$this->logger->notice("Create LDAP group '$gid' ($newGroupDN)");
			return $newGroupDN;
		} else {
			$this->logger->error("Unable to create LDAP group '$gid' ($newGroupDN)");
			return null;
		}
	}

	/**
	 * delete a group
	 *
	 * @param string $gid gid of the group to delete
	 * @throws Exception
	 */
	public function deleteGroup($gid): bool {
		$connection = $this->ldapProvider->getGroupLDAPConnection($gid);
		$groupDN = $this->ldapProvider->getGroupDN($gid);

		if (!$ret = ldap_delete($connection, $groupDN)) {
			$this->logger->error('Unable to delete LDAP Group: ' . $gid);
		} else {
			$this->logger->notice('Delete LDAP Group: ' . $gid);
		}
		return $ret;
	}

	/**
	 * Add a LDAP user to a LDAP group
	 *
	 * @param string $uid Name of the user to add to group
	 * @param string $gid Name of the group in which add the user
	 *
	 * Adds a LDAP user to a LDAP group.
	 * @throws Exception
	 */
	public function addToGroup($uid, $gid): bool {
		$connection = $this->ldapProvider->getGroupLDAPConnection($gid);
		$groupDN = $this->ldapProvider->getGroupDN($gid);

		$entry = [];
		$attribute = strtolower($this->ldapProvider->getLDAPGroupMemberAssoc($gid));
		switch ($attribute) {
			case 'memberuid':
				$entry[$attribute] = $uid;
				break;
			case 'gidnumber':
				throw new Exception('Cannot add to group when gidNumber is used as relation');
				break;
			default:
				$this->logger->notice('Unexpected attribute {attribute} as group member association.', ['attribute' => $attribute]);
			case 'uniquemember':
			case 'member':
				$entry[$attribute] = $this->ldapProvider->getUserDN($uid);
				break;
		}

		if (!$ret = ldap_mod_add($connection, $groupDN, $entry)) {
			$this->logger->error('Unable to add user ' . $uid . ' to group ' . $gid);
		} else {
			$this->logger->notice('Add user: ' . $uid . ' to group: ' . $gid);
		}
		return $ret;
	}

	/**
	 * Removes a LDAP user from a LDAP group
	 *
	 * @param string $uid Name of the user to remove from group
	 * @param string $gid Name of the group from which remove the user
	 *
	 * removes the user from a group.
	 * @throws Exception
	 */
	public function removeFromGroup($uid, $gid): bool {
		$connection = $this->ldapProvider->getGroupLDAPConnection($gid);
		$groupDN = $this->ldapProvider->getGroupDN($gid);

		$entry = [];
		$attribute = strtolower($this->ldapProvider->getLDAPGroupMemberAssoc($gid));
		switch ($attribute) {
			case 'memberuid':
				$entry[$attribute] = $uid;
				break;
			case 'gidnumber':
				throw new Exception('Cannot remove from group when gidNumber is used as relation');
				break;
			default:
				$this->logger->notice('Unexpected attribute {attribute} as group member association.', ['attribute' => $attribute]);
			case 'uniquemember':
			case 'member':
				$entry[$attribute] = $this->ldapProvider->getUserDN($uid);
				break;
		}

		if (!$ret = ldap_mod_del($connection, $groupDN, $entry)) {
			$this->logger->error('Unable to remove user: ' . $uid . ' from group: ' . $gid);
		} else {
			$this->logger->notice('Remove user: ' . $uid . ' from group: ' . $gid);
		}
		return $ret;
	}


	public function countUsersInGroup($gid, $search = ''): bool {
		return false;
	}

	public function getGroupDetails($gid): bool {
		return false;
	}

	public function isLDAPGroup($gid): bool {
		try {
			return !empty($this->ldapProvider->getGroupDN($gid));
		} catch (Exception) {
			return false;
		}
	}

	private function buildNewEntry(string $gid, string $attribute): array {
		$entry = [
			'objectClass' => [],
			'cn' => $gid,
		];
		switch ($attribute) {
			case 'memberuid':
			case 'gidnumber':
				$entry['objectClass'][] = 'posixGroup';
				break;
			default:
				$this->logger->notice('Unexpected attribute {attribute} as group member association.', ['attribute' => $attribute]);
			case 'uniquemember':
			case 'member':
				$entry['objectClass'][] = 'groupOfNames';
				$entry[$attribute] = [''];
				break;
		}
		return $entry;
	}

	public function makeLdapBackendFirst(): void {
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
