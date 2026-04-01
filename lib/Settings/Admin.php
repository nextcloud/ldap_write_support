<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\LdapWriteSupport\Settings;

use OCA\LdapWriteSupport\AppInfo\Application;
use OCA\LdapWriteSupport\Service\Configuration;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\Settings\ISettings;
use OCP\Util;

class Admin implements ISettings {
	public function __construct(
		private IInitialState $initialStateService,
		private Configuration $config,
	) {
	}

	/**
	 * @return TemplateResponse returns the instance with all parameters set, ready to be rendered
	 * @since 9.1
	 */
	#[\Override]
	public function getForm(): TemplateResponse {
		$this->initialStateService->provideInitialState(
			'templates',
			[
				'user' => $this->config->getUserTemplate(),
				'userDefault' => $this->config->getUserTemplateDefault(),
			]
		);
		$this->initialStateService->provideInitialState(
			'switches',
			[
				'createRequireActorFromLdap' => $this->config->isLdapActorRequired(),
				'createPreventFallback' => $this->config->isPreventFallback(),
				'hasAvatarPermission' => $this->config->hasAvatarPermission(),
				'hasPasswordPermission' => $this->config->hasPasswordPermission(),
				'newUserRequireEmail' => $this->config->isRequireEmail(),
				'newUserGenerateUserID' => $this->config->isGenerateUserId(),
				'useUnicodePassword' => $this->config->useUnicodePassword(),
			]
		);

		Util::addScript(Application::APP_ID, 'ldap_write_support-admin-settings');
		Util::addStyle(Application::APP_ID, 'ldap_write_support-admin-settings');

		return new TemplateResponse(Application::APP_ID, 'settings-admin');
	}

	#[\Override]
	public function getSection(): string {
		return 'ldap';
	}

	#[\Override]
	public function getPriority(): int {
		return 35;
	}
}
