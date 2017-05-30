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


use OCA\User_LDAP\Access;
use OCA\User_LDAP\User_LDAP;
use OCP\IConfig;
use OCP\Notification\IManager as INotificationManager;
use OC\User\Backend;


class User_LDAP_Edit extends User_LDAP {
	public function __construct(Access $access, IConfig $ocConfig, INotificationManager $notificationManager) {
		parent::__construct($access, $ocConfig, $notificationManager);
	}

	public function implementsActions($actions) {
		return (bool)((Backend::CHECK_PASSWORD
				| Backend::GET_HOME
				| Backend::GET_DISPLAYNAME
				| Backend::PROVIDE_AVATAR
				| Backend::COUNT_USERS
				| Backend::SET_DISPLAYNAME
				| ((intval($this->access->connection->turnOnPasswordChange) === 1)?(Backend::SET_PASSWORD):0))
			& $actions);	}

	public function setDisplayName($displayName) {
	}
}