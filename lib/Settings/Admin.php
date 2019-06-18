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

namespace OCA\LdapWriteSupport\Settings;

use OCA\LdapWriteSupport\AppInfo\Application;
use OCA\LdapWriteSupport\Service\Configuration;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IInitialStateService;
use OCP\Settings\ISettings;

class Admin implements ISettings {
	/** @var Configuration */
	private $config;

	/** @var IInitialStateService */
	private $initialStateService;

	public function __construct(IInitialStateService $initialStateService, Configuration $config) {
		$this->initialStateService = $initialStateService;
		$this->config = $config;
	}

	/**
	 * @return TemplateResponse returns the instance with all parameters set, ready to be rendered
	 * @since 9.1
	 */
	public function getForm() {
		$this->initialStateService->provideInitialState(
			Application::APP_ID,
			'templates',
			[
				'user' => $this->config->getUserTemplate(),
				'userDefault' => $this->config->getUserTemplateDefault(),
			]
		);
		$this->initialStateService->provideInitialState(
			Application::APP_ID,
			'switches',
			[
				'createRequireActorFromLdap' => $this->config->isLdapActorRequired(),
				'createPreventFallback' => $this->config->isPreventFallback(),
				'newUserRequireEmail' => $this->config->isRequireEmail(),
				'newUserGenerateUserID' => $this->config->isGenerateUserId(),
			]
		);
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
	 * the admin section. The forms are arranged in ascending order of the
	 * priority values. It is required to return a value between 0 and 100.
	 *
	 * E.g.: 70
	 * @since 9.1
	 */
	public function getPriority() {
		return 35;
	}
}
