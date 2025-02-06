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
	/** @var IInitialState */
	private $initialStateService;

	public function __construct(
		IInitialState $initialStateService,
		private Configuration $config,
	) {
		$this->initialStateService = $initialStateService;
	}

	/**
	 * @return TemplateResponse returns the instance with all parameters set, ready to be rendered
	 * @since 9.1
	 */
	public function getForm() {
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

	/**
	 * @return string the section ID, e.g. 'sharing'
	 * @since 9.1
	 */
	public function getSection() {
		return 'ldap';
	}

	/**
	 * @return int whether the form should be rather on the top or bottom of
	 *             the admin section. The forms are arranged in ascending order of the
	 *             priority values. It is required to return a value between 0 and 100.
	 *
	 * E.g.: 70
	 * @since 9.1
	 */
	public function getPriority() {
		return 35;
	}
}
