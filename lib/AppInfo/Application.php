<?php

declare(strict_types=1);

namespace OCA\LdapWriteSupport\AppInfo;

use OC;
use OC\Group\Group;
use OC\User\User;
use OCA\LdapWriteSupport\LDAPUserManager;
use OCA\LdapWriteSupport\LDAPGroupManager;
use OCA\User_LDAP\GroupPluginManager;
use OCA\User_LDAP\UserPluginManager;
use OCP\AppFramework\App;

class Application extends App {
	/** @var LDAPUserManager */
	protected $ldapUserManager;

	/** @var LDAPGroupManager */
	protected $ldapGroupManager;

	/** @var bool */
	protected $ldapEnabled = false;

	public function __construct(array $urlParams = []) {
		parent::__construct('ldap_write_support', $urlParams);
		$this->ldapEnabled = OC::$server->getAppManager()->isEnabledForUser('user_ldap');
	}

	public function registerLDAPPlugins(): void {
		if(!$this->ldapEnabled) {
			return;
		}

		$this->ldapUserManager = OC::$server->query(LDAPUserManager::class);
		$this->ldapGroupManager = OC::$server->query(LDAPGroupManager::class);

		$userPluginManager = OC::$server->query(UserPluginManager::class);
		$groupPluginManager = OC::$server->query(GroupPluginManager::class);

		$userPluginManager->register($this->ldapUserManager);
		$groupPluginManager->register($this->ldapGroupManager);
	}

	public function registerHooks(): void {
		if(!$this->ldapEnabled) {
			return;
		}

		$subAdmin = OC::$server->getGroupManager()->getSubAdmin();

		$subAdmin->listen('\OC\SubAdmin', 'postCreateSubAdmin', function (User $user, Group $group) {
			if ($user->getBackendClassName() == "LDAP" and $this->ldapGroupManager->isLDAPGroup($group->getGID())) {
				$this->ldapGroupManager->addOwnerToGroup($user->getUID(), $group->getGID());
			}
		});

		$subAdmin->listen('\OC\SubAdmin', 'postDeleteSubAdmin', function (User $user, Group $group) {
			if ($user->getBackendClassName() == "LDAP" and $this->ldapGroupManager->isLDAPGroup($group->getGID())) {
				$this->ldapGroupManager->removeOwnerFromGroup($user->getUID(), $group->getGID());
			}
		});
	}
}
