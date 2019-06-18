<?php
declare(strict_types=1);
/**
 * @copyright Copyright (c) 2019 Arthur Schiwon <blizzz@arthur-schiwon.de>
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

namespace OCA\LdapWriteSupport\Service;

use OCA\LdapWriteSupport\AppInfo\Application;
use OCP\IConfig;

class Configuration {

	/** @var IConfig */
	private $config;

	public function __construct(IConfig $config) {
		$this->config = $config;
	}

	public function isLdapActorRequired(): bool {
		return $this->config->getAppValue('ldap_write_support', 'createRequireActorFromLdap', '0') === '1';
	}

	public function isPreventFallback(): bool {
		return $this->config->getAppValue('ldap_write_support', 'createPreventFallback', '1') === '1';
	}

	public function hasAvatarPermission(): bool {
		return $this->config->getAppValue('ldap_write_support', 'hasAvatarPermission', '1') === '1';
	}

	public function getUserTemplate() {
		return $this->config->getAppValue(
			Application::APP_ID,
			'template.user',
			$this->getUserTemplateDefault()
		);
	}

	public function getUserTemplateDefault() {
		return
			'dn: uid={RND_UID},{BASE}' . PHP_EOL .
			'objectClass: inetOrgPerson' . PHP_EOL .
			'objectClass: person' . PHP_EOL .
			'uid: {RND_UID}' . PHP_EOL .
			'displayName: {UID}' . PHP_EOL .
			'cn: {UID}' . PHP_EOL .
			'sn: {UID}' . PHP_EOL .
			'userPassword: {PWD}';
	}

	public function isRequireEmail(): bool {
		// this core settings flag is not exposed anywhere else
		return $this->config->getAppValue('settings', 'newUser.requireEmail', '0') === '1';
	}

	public function isGenerateUserId(): bool {
		// this core settings flag is not exposed anywhere else
		return $this->config->getAppValue('settings', 'newUser.generateUserID', '0') === '1';
	}
}
