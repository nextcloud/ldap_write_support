<?php

/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2017 Cooperativa EITA <eita.org.br>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\LdapWriteSupport;

use LDAP\Connection;
use OC\ServerNotAvailableException;
use OCA\LdapWriteSupport\AppInfo\Application;
use OCA\User_LDAP\Configuration;
use OCA\User_LDAP\Helper;
use Psr\Log\LoggerInterface;

class LDAPConnect {
	/** @var Configuration */
	private $ldapConfig;
	/** @var bool|null */
	private $passwdSupport;

	public function __construct(
		Helper $ldapBackendHelper,
		private LoggerInterface $logger,
	) {
		$this->passwdSupport = null;
		$ldapConfigPrefixes = $ldapBackendHelper->getServerConfigurationPrefixes(true);
		$prefix = array_shift($ldapConfigPrefixes);
		$this->ldapConfig = new Configuration($prefix);
	}

	/**
	 * @return resource|Connection
	 * @throws ServerNotAvailableException
	 */
	public function connect() {
		$ldapHost = $this->ldapConfig->ldapHost;
		$ldapPort = $this->ldapConfig->ldapPort;

		// shamelessly copied from OCA\User_LDAP\LDAP::connect()
		$pos = strpos($ldapHost, '://');
		if ($pos === false) {
			$ldapHost = 'ldap://' . $ldapHost;
			$pos = 4;
		}
		if (!str_contains(substr($ldapHost, $pos + 1), ':') && !empty($ldapPort)) {
			$ldapHost .= ':' . $ldapPort;
		}

		// Connecting to LDAP - TODO: connect directly via LDAP plugin
		$cr = ldap_connect($ldapHost);
		if (!is_resource($cr) && !is_object($cr)) {
			$this->logger->error('Unable to connect to LDAP host {ldapHost}:{ldapPort}',
				[
					'app' => Application::APP_ID,
					'ldapHost' => $ldapHost,
					'ldapPort' => $ldapPort,
				]);
			throw new ServerNotAvailableException('LDAP server not available');
		}

		ldap_set_option($cr, LDAP_OPT_PROTOCOL_VERSION, 3);
		$this->logger->debug('Connected to LDAP host {ldapHost}:{ldapPort}',
			[
				'app' => Application::APP_ID,
				'ldapHost' => $ldapHost,
				'ldapPort' => $ldapPort,
			]);
		return $cr;
	}

	/**
	 * @return false|resource|Connection
	 * @throws ServerNotAvailableException
	 */
	public function bind() {
		$ds = $this->connect();
		$dn = $this->ldapConfig->ldapAgentName;
		$secret = $this->ldapConfig->ldapAgentPassword;

		if (!ldap_bind($ds, $dn, $secret)) {
			$this->logger->error('Unable to bind to LDAP server',
				['app' => Application::APP_ID]
			);
			return false;
		} else {
			$this->logger->debug('Bound to LDAP server using credentials for {dn}', [
				'app' => Application::APP_ID,
				'dn' => $dn,
			]);
			return $ds;
		}
	}

	/**
	 * @return false|resource|Connection
	 * @throws ServerNotAvailableException
	 */
	public function getLDAPConnection() {
		return $this->bind();
	}

	public function getLDAPBaseUsers(): array {
		$bases = $this->ldapConfig->ldapBaseUsers;
		if (empty($bases)) {
			$bases = $this->ldapConfig->ldapBase;
		}
		return $bases;
	}

	public function getLDAPBaseGroups(): array {
		$bases = $this->ldapConfig->ldapBaseGroups;
		if (empty($bases)) {
			$bases = $this->ldapConfig->ldapBase;
		}
		return $bases;
	}

	public function getDisplayNameAttribute(): string {
		return $this->ldapConfig->ldapUserDisplayName;
	}

	public function groupsEnabled(): bool {
		$filter = trim((string)$this->ldapConfig->ldapGroupFilter);
		$gAssoc = trim((string)$this->ldapConfig->ldapGroupMemberAssocAttr);

		return $filter !== '' && $gAssoc !== '';
	}

	public function hasPasswordPolicy(): bool {
		$ppDN = $this->ldapConfig->ldapDefaultPPolicyDN;
		return !empty($ppDN);
	}

	/**
	 * checks whether the LDAP server supports the passwd exop
	 *
	 * @param Connection $connection LDAP connection to check
	 * @return boolean either the user can or cannot
	 */
	public function hasPasswdExopSupport($connection): bool {
		// TODO: We should cache this by ldap prefix, but currently we have no access to it.
		if (is_null($this->passwdSupport)) {
			$ret = ldap_read($connection, '', '(objectclass=*)', ['supportedExtension']);
			if ($ret === false) {
				$this->passwdSupport = false;
				$this->logger->debug(
					'Could not check passwd_exop support of LDAP host, host does not provide the supportedExtension entry.',
					[ 'app' => Application::APP_ID ]
				);
				return false;
			}

			$ret = ldap_first_entry($connection, $ret);
			if ($ret === false) {
				$this->passwdSupport = false;
				$this->logger->error(
					'Could not check passwd_exop support of LDAP host, host returned malformed data for the supported ldap extension entry.',
					[ 'app' => Application::APP_ID ]
				);
				return false;
			}

			$values = ldap_get_values($connection, $ret, 'supportedExtension');
			$this->passwdSupport = ($values !== false) && in_array(LDAP_EXOP_MODIFY_PASSWD, $values);
		}
		return $this->passwdSupport;
	}
}
