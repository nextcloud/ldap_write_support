<?php
/**
 * @author Alan Tygel <alan@eita.org.br>
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
use OCP\AppFramework\Http\DataResponse;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IRequest;

/**
 * Class ThemingController
 *
 * handle ajax requests to update the theme
 *
 * @package OCA\Ldapusermanagement\Controller
 */
class LdapusermanagementController extends Controller {
	/** @var ThemingDefaults */
	private $appName;	
	private $template;
	// /** @var Util */
	// private $util;
	/** @var ITimeFactory */
	private $timeFactory;
	/** @var IL10N */
	private $l;
	/** @var IConfig */
	private $config;

	/**
	 * LdapusermanagementController constructor.
	 *
	 * @param string $appName
	 * @param IRequest $request
	 * @param IConfig $config
	 * @param LdapusermanagementDefaults $template
	 * @param IL10N $l
	 */
	public function __construct(
		$appName,
		// IRequest $request,
		IConfig $config,
		LdapusermanagementDefaults $template,
		// Util $util,
		IL10N $l
		// ITempManager $tempManager
	) {
		parent::__construct($appName, $request);

		$this->appName = $appName;
		$this->template = $template;
		// $this->util = $util;
		$this->l = $l;
		$this->config = $config;
		// $this->tempManager = $tempManager;
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
			case 'host':
				if (strlen($value) > 250) {
					return new DataResponse([
						'data' => [
							'message' => $this->l->t('The host name is too long'),
						],
						'status' => 'error'
					]);
				}
				break;
			case 'dn':
				if (strlen($value) > 500) {
					return new DataResponse([
						'data' => [
							'message' => $this->l->t('The given dn is too long'),
						],
						'status' => 'error'
					]);
				}
				break;
			case 'password':
				if (strlen($value) > 500) {
					return new DataResponse([
						'data' => [
							'message' => $this->l->t('The given password is too long'),
						],
						'status' => 'error'
					]);
				}
				break;
			case 'port':
				if (!preg_match('/^[0-9]{3,4}$/i', $value)) {
					return new DataResponse([
						'data' => [
							'message' => $this->l->t('The given port is invalid'),
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