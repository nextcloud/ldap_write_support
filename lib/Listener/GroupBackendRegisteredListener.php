<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2020 Arthur Schiwon <blizzz@arthur-schiwon.de>
 *
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\LdapWriteSupport\Listener;

use OCA\LdapWriteSupport\LDAPGroupManager;
use OCA\User_LDAP\Events\GroupBackendRegistered;
use OCP\App\IAppManager;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;

/**
 * @template-implements IEventListener<GroupBackendRegistered>
 */
class GroupBackendRegisteredListener implements IEventListener {
	/** @var IAppManager */
	private $appManager;
	/** @var LDAPGroupManager */
	private $ldapGroupManager;

	public function __construct(IAppManager $appManager, LDAPGroupManager $ldapGroupManager) {
		$this->appManager = $appManager;
		$this->ldapGroupManager = $ldapGroupManager;
	}

	/**
	 * @inheritDoc
	 */
	public function handle(Event $event): void {
		if (!$event instanceof GroupBackendRegistered
			|| !$this->appManager->isEnabledForUser('user_ldap')
		) {
			return;
		}
		$event->getPluginManager()->register($this->ldapGroupManager);
	}
}
