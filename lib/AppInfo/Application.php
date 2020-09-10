<?php

declare(strict_types=1);

namespace OCA\LdapWriteSupport\AppInfo;

use OCA\LdapWriteSupport\LDAPUserManager;
use OCA\LdapWriteSupport\LDAPGroupManager;
use OCA\LdapWriteSupport\Listener\UserBackendRegisteredListener;
use OCA\LdapWriteSupport\Listener\GroupBackendRegisteredListener;
use OCA\User_LDAP\Events\GroupBackendRegistered;
use OCA\User_LDAP\Events\UserBackendRegistered;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;

class Application extends App implements IBootstrap {
	/** @var LDAPUserManager */
	protected $ldapUserManager;

	/** @var LDAPGroupManager */
	protected $ldapGroupManager;

	public const APP_ID = 'ldap_write_support';

	public function __construct(array $urlParams = []) {
		parent::__construct(self::APP_ID, $urlParams);
	}

	public function register(IRegistrationContext $context): void {
		$context->registerEventListener(UserBackendRegistered::class, UserBackendRegisteredListener::class);
		$context->registerEventListener(GroupBackendRegistered::class, GroupBackendRegisteredListener::class);
	}

	public function boot(IBootContext $context): void {
	}
}
