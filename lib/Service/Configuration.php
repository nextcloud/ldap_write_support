<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
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

	public function hasPasswordPermission(): bool {
		return $this->config->getAppValue('ldap_write_support', 'hasPasswordPermission', '1') === '1';
	}

	public function useUnicodePassword(): bool {
		return $this->config->getAppValue('ldap_write_support', 'useUnicodePassword', '0') === '1';
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
			'dn: uid={UID},{BASE}' . PHP_EOL .
			'objectClass: inetOrgPerson' . PHP_EOL .
			'uid: {UID}' . PHP_EOL .
			'displayName: {UID}' . PHP_EOL .
			'cn: {UID}' . PHP_EOL .
			'sn: {UID}';
	}

	public function isRequireEmail(): bool {
		// this core settings flag is not exposed anywhere else
		return $this->config->getAppValue('core', 'newUser.requireEmail', 'no') === 'yes';
	}

	public function isGenerateUserId(): bool {
		// this core settings flag is not exposed anywhere else
		return $this->config->getAppValue('core', 'newUser.generateUserID', 'no') === 'yes';
	}
}
