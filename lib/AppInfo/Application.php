<?php

declare(strict_types=1);

namespace OCA\LdapWriteSupport\AppInfo;

use OC;
use OC\Group\Group;
use OC\User\User;
use OCA\LdapWriteSupport\LDAPConnect;
use OCA\LdapWriteSupport\LDAPUserManager;
use OCA\LdapWriteSupport\LDAPGroupManager;
use OCA\LdapWriteSupport\Service\Configuration;
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

	public const APP_ID = 'ldap_write_support';

	public function __construct(array $urlParams = []) {
		parent::__construct(self::APP_ID, $urlParams);
		$this->ldapEnabled = OC::$server->getAppManager()->isEnabledForUser('user_ldap');
	}

	public function registerLDAPPlugins(): void {
		if(!$this->ldapEnabled) {
			return;
		}

		\OC_App::loadApp('user_ldap');
		$c = $this->getContainer();
		$s = $this->getContainer()->getServer();
		$p = $s->getLDAPProvider();

		// resolving LDAP provider fails indeed

		$this->ldapUserManager = new LDAPUserManager(
			$s->getUserManager(),
			$s->getGroupManager(),
			$s->getUserSession(),
			new LDAPConnect($s->getConfig()),
			$s->getConfig(),
			$p,
			$c->query(Configuration::class)
		);

//		$this->ldapUserManager = $c->query(LDAPUserManager::class);
		$this->ldapGroupManager = $c->query(LDAPGroupManager::class);

		/** @var UserPluginManager $userPluginManager */
		$userPluginManager = OC::$server->query('LDAPUserPluginManager');
		/** @var GroupPluginManager $groupPluginManager */
		$groupPluginManager = OC::$server->query('LDAPGroupPluginManager');

		$userPluginManager->register($this->ldapUserManager);
		$groupPluginManager->register($this->ldapGroupManager);
	}

	public function registerHooks(): void {
		if(!$this->ldapEnabled) {
			return;
		}

		$subAdmin = OC::$server->getGroupManager()->getSubAdmin();

		$subAdmin->listen('\OC\SubAdmin', 'postCreateSubAdmin', function (User $user, Group $group) {
			if ($user->getBackendClassName() === "LDAP" and $this->ldapGroupManager->isLDAPGroup($group->getGID())) {
				$this->ldapGroupManager->addOwnerToGroup($user->getUID(), $group->getGID());
			}
		});

		$subAdmin->listen('\OC\SubAdmin', 'postDeleteSubAdmin', function (User $user, Group $group) {
			if ($user->getBackendClassName() === "LDAP" and $this->ldapGroupManager->isLDAPGroup($group->getGID())) {
				$this->ldapGroupManager->removeOwnerFromGroup($user->getUID(), $group->getGID());
			}
		});
	}
}
