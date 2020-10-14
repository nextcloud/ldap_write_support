<?php

declare(strict_types=1);

namespace OCA\LdapWriteSupport\AppInfo;

use Exception;
use OC;
use OC\Group\Group;
use OC\User\User;
use OCA\LdapWriteSupport\LDAPConnect;
use OCA\LdapWriteSupport\LDAPUserManager;
use OCA\LdapWriteSupport\LDAPGroupManager;
use OCA\LdapWriteSupport\Service\Configuration;
use OCA\User_LDAP\GroupPluginManager;
use OCA\User_LDAP\Helper;
use OCA\User_LDAP\UserPluginManager;
use OCP\AppFramework\App;
use OCP\AppFramework\QueryException;

class Application extends App {
	/** @var LDAPUserManager */
	protected $ldapUserManager;

	/** @var LDAPGroupManager */
	protected $ldapGroupManager;

	/** @var bool */
	protected $ldapEnabled = false;

	const APP_ID = 'ldap_write_support';

	public function __construct(array $urlParams = []) {
		parent::__construct(self::APP_ID, $urlParams);
		$this->ldapEnabled = OC::$server->getAppManager()->isEnabledForUser('user_ldap');
	}

	/**
	 * @throws QueryException
	 * @throws Exception
	 */
	public function registerLDAPPlugins(): void {
		if(!$this->ldapEnabled) {
			return;
		}

		if(!\OC_App::isAppLoaded('user_ldap')) {
			\OC_App::loadApp('user_ldap');
		}
		$c = $this->getContainer();
		$s = $this->getContainer()->getServer();
		try {
			$provider = $s->getLDAPProvider();
		} catch (\Exception $e) {
			if(strpos($e->getMessage(), 'user_ldap app must be enabled') !== false) {
				$s->getLogger()->info (
					'Not registering plugins, because there are no active LDAP configs',
					['app' => self::APP_ID]
				);
				return;
			}
			throw $e;
		}

		$ldapConnect = new LDAPConnect($s->query(Helper::class), $s->getLogger());

		// resolving LDAP provider fails indeed
		$this->ldapUserManager = new LDAPUserManager(
			$s->getUserManager(),
			$s->getUserSession(),
			$ldapConnect,
			$provider,
			$c->query(Configuration::class),
			$s->getL10N(self::APP_ID),
			$s->getLogger()
		);

		$this->ldapGroupManager = new LDAPGroupManager(
			$s->getGroupManager(),
			$s->getUserSession(),
			$ldapConnect,
			$provider,
			$c->query(Configuration::class),
			$s->getL10N(self::APP_ID),
			$s->getLogger()
		);

		/** @var UserPluginManager $userPluginManager */
		$userPluginManager = OC::$server->query(UserPluginManager::class);
		/** @var GroupPluginManager $groupPluginManager */
		$groupPluginManager = OC::$server->query(GroupPluginManager::class);

		$userPluginManager->register($this->ldapUserManager);
		$groupPluginManager->register($this->ldapGroupManager);
	}

}
