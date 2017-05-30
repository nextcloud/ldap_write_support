<?php
/**
 * @author Vinicius Brand <vinicius@eita.org.br>
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

namespace OCA\ldapusermanagement\lib;


use OCA\User_LDAP\ILDAPWrapper;
use OCA\User_LDAP\User_Proxy;
use OCP\IConfig;
use OCP\Notification\IManager as INotificationManager;

class User_Proxy_Edit extends User_Proxy {
	public function __construct(array $serverConfigPrefixes, ILDAPWrapper $ldap, IConfig $ocConfig, INotificationManager $notificationManager) {
		parent::__construct($serverConfigPrefixes, $ldap, $ocConfig, $notificationManager);
	}

	public function setDisplayName($displayName) {
		$this->refBackend->setDisplayName();
	}
}