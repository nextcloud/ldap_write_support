<?php
/**
 * @copyright Copyright (c) 2016 Bjoern Schiessle <bjoern@schiessle.org>
 * @copyright Copyright (c) 2016 Lukas Reschke <lukas@statuscode.ch>
 *
 * @author Bjoern Schiessle <bjoern@schiessle.org>
 * @author Julius Haertl <jus@bitgrid.net>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author oparoz <owncloud@interfasys.ch>
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

namespace OCA\Ldapusermanagement\Controller;

use OCA\Ldapusermanagement\LdapusermanagementDefaults;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataDownloadResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\NotFoundResponse;
use OCP\AppFramework\Http\StreamResponse;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\Files\File;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IRequest;
use OCA\Theming\Util;
use OCP\ITempManager;

/**
 * Class ThemingController
 *
 * handle ajax requests to update the theme
 *
 * @package OCA\Theming\Controller
 */
class LdapusermanagementController extends Controller {
	/** @var ThemingDefaults */
	private $template;
	/** @var Util */
	private $util;
	/** @var ITimeFactory */
	private $timeFactory;
	/** @var IL10N */
	private $l;
	/** @var IConfig */
	private $config;
	/** @var IRootFolder */
	private $rootFolder;
	/** @var ITempManager */
	private $tempManager;

	/**
	 * ThemingController constructor.
	 *
	 * @param string $appName
	 * @param IRequest $request
	 * @param IConfig $config
	 * @param ThemingDefaults $template
	 * @param Util $util
	 * @param ITimeFactory $timeFactory
	 * @param IL10N $l
	 * @param IRootFolder $rootFolder
	 * @param ITempManager $tempManager
	 */
	public function __construct(
		$appName,
		IRequest $request,
		IConfig $config,
		LdapusermanagementDefaults $template,
		Util $util,
		ITimeFactory $timeFactory,
		IL10N $l,
		IRootFolder $rootFolder,
		ITempManager $tempManager
	) {
		parent::__construct($appName, $request);

		$this->template = $template;
		$this->util = $util;
		$this->timeFactory = $timeFactory;
		$this->l = $l;
		$this->config = $config;
		$this->rootFolder = $rootFolder;
		$this->tempManager = $tempManager;
	}

	/**
	 * @param string $setting
	 * @param string $value
	 * @return DataResponse
	 * @internal param string $color
	 */
	public function updateStylesheet($setting, $value) {
		$value = trim($value);
		switch ($setting) {
			case 'name':
				if (strlen($value) > 250) {
					return new DataResponse([
						'data' => [
							'message' => $this->l->t('The given name is too long'),
						],
						'status' => 'error'
					]);
				}
				break;
			case 'url':
				if (strlen($value) > 500) {
					return new DataResponse([
						'data' => [
							'message' => $this->l->t('The given web address is too long'),
						],
						'status' => 'error'
					]);
				}
				break;
			case 'slogan':
				if (strlen($value) > 500) {
					return new DataResponse([
						'data' => [
							'message' => $this->l->t('The given slogan is too long'),
						],
						'status' => 'error'
					]);
				}
				break;
			case 'color':
				if (!preg_match('/^\#([0-9a-f]{3}|[0-9a-f]{6})$/i', $value)) {
					return new DataResponse([
						'data' => [
							'message' => $this->l->t('The given color is invalid'),
						],
						'status' => 'error'
					]);
				}
				break;
		}

		$this->template->set($setting, $value);
		return new DataResponse(
			[
				'data' =>
					[
						'message' => $this->l->t('Saved')
					],
				'status' => 'success'
			]
		);
	}



	/**
	 * Revert setting to default value
	 *
	 * @param string $setting setting which should be reverted
	 * @return DataResponse
	 */
	public function undo($setting) {
		$value = $this->template->undo($setting);
		return new DataResponse(
			[
				'data' =>
					[
						'value' => $value,
						'message' => $this->l->t('Saved')
					],
				'status' => 'success'
			]
		);
	}
}