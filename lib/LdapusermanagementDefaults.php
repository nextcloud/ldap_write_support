<?php
/**
 * @copyright Copyright (c) 2016 Bjoern Schiessle <bjoern@schiessle.org>
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


namespace OCA\Ldapusermanagement;

use OCP\IConfig;
use OCP\IL10N;

class LdapusermanagementDefaults extends \OC_Defaults {

	/** @var IConfig */
	private $config;
	/** @var IL10N */
	private $l;
	/** @var string */
	private $host;
	/** @var string */
	private $port;
	/** @var string */
	private $dn;
	/** @var string */
	private $password;
	/** @var string */
	private $userbase;

	/**
	 * ThemingDefaults constructor.
	 *
	 * @param IConfig $config
	 * @param IL10N $l
	 * @param IURLGenerator $urlGenerator
	 * @param \OC_Defaults $defaults
	 * @param IRootFolder $rootFolder
	 * @param ICacheFactory $cacheFactory
	 */
	public function __construct(IConfig $config,
								IL10N $l
	) {
		parent::__construct();
		$this->config = $config;
		$this->l = $l;

		$this->host = '';//$defaults->getHost();
		$this->port = '';//$defaults->getPort();
		$this->dn = '';//$defaults->getDN();
		$this->password = '';//$defaults->getPassword();
	}

	public function getHost() {
		return $this->config->getAppValue('ldapusermanagement', 'host', $this->host);
	}

	public function getPort() {
		return $this->config->getAppValue('ldapusermanagement', 'port', $this->port);
	}

	public function getDN() {
		return $this->config->getAppValue('ldapusermanagement', 'dn', $this->dn);
	}

	public function getPassword() {
		return $this->config->getAppValue('ldapusermanagement', 'password', $this->password);
	}

	public function getUserbase() {
		return $this->config->getAppValue('ldapusermanagement', 'userbase', $this->userbase);
	}

	public function getGroupbase() {
		return $this->config->getAppValue('ldapusermanagement', 'groupbase', $this->groupbase);
	}


	/**
	 * Increases the cache buster key
	 */
	private function increaseCacheBuster() {
		$cacheBusterKey = $this->config->getAppValue('ldapusermanagement', 'cachebuster', '0');
		$this->config->setAppValue('ldapusermanagement', 'cachebuster', (int)$cacheBusterKey+1);
	}

	/**
	 * Update setting in the database
	 *
	 * @param string $setting
	 * @param string $value
	 */
	public function set($setting, $value) {
		$this->config->setAppValue('ldapusermanagement', $setting, $value);
		$this->increaseCacheBuster();
	}

	/**
	 * Revert settings to the default value
	 *
	 * @param string $setting setting which should be reverted
	 * @return string default value
	 */
	public function undo($setting) {
		$this->config->deleteAppValue('ldapusermanagement', $setting);
		$this->increaseCacheBuster();

		switch ($setting) {
			case 'host':
				$returnValue = $this->getHost();
				break;
			case 'port':
				$returnValue = $this->getPort();
				break;
			case 'dn':
				$returnValue = $this->getDN();
			case 'password':
				$returnValue = $this->getPassword();
				break;
			case 'userbase':
				$returnValue = $this->getUserbase();
				break;
			case 'groupbase':
				$returnValue = $this->getGroupbase();
				break;
			default:
				$returnValue = '';
				break;
		}

		return $returnValue;
	}

}
