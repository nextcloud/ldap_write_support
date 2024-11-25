<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\LdapWriteSupport\Listener;

use OCA\LdapWriteSupport\LDAPUserManager;
use OCA\User_LDAP\Events\UserBackendRegistered;
use OCP\App\IAppManager;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;

/**
 * @template-implements IEventListener<UserBackendRegistered>
 */
class UserBackendRegisteredListener implements IEventListener {
	/** @var IAppManager */
	private $appManager;

	public function __construct(
		IAppManager $appManager,
		private LDAPUserManager $ldapUserManager,
	) {
		$this->appManager = $appManager;
	}

	/**
	 * @inheritDoc
	 */
	public function handle(Event $event): void {
		if (!$event instanceof UserBackendRegistered
			|| !$this->appManager->isEnabledForUser('user_ldap')
		) {
			return;
		}
		$event->getPluginManager()->register($this->ldapUserManager);
	}
}
