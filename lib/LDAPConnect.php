<?php
/**
 * @author Alan Tygel <alan@eita.org.br>
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

namespace OCA\LdapWriteSupport;

use OC\ServerNotAvailableException;
use OCA\LdapWriteSupport\AppInfo\Application;
use OCA\User_LDAP\Configuration;
use OCA\User_LDAP\Helper;

class LDAPConnect {
	/** @var Configuration */
	private $ldapConfig;

	public function __construct(Helper $ldapBackendHelper) {
		$ldapConfigPrefixes = $ldapBackendHelper->getServerConfigurationPrefixes(true);
		$prefix = array_shift($ldapConfigPrefixes);
		$this->ldapConfig = new Configuration($prefix);
	}

	/**
	 * @return bool|resource
	 * @throws ServerNotAvailableException
	 */
	public function connect() {
		$ldapHost = $this->ldapConfig->ldapHost;
		$ldapPort = $this->ldapConfig->ldapPort;

        // Connecting to LDAP - TODO: connect directly via LDAP plugin
        $cr = ldap_connect($ldapHost, $ldapPort);
        if(!is_resource($cr)) {
			throw new ServerNotAvailableException('LDAP server not available');
		}

        if ($cr) {
            ldap_set_option($cr, LDAP_OPT_PROTOCOL_VERSION, 3);
            $message = "Connected to LDAP host $ldapHost:$ldapPort";
            \OC::$server->getLogger()->notice($message, ['app' => Application::APP_ID]);
            return $cr;
        } else {
            $message = "Unable to connect to LDAP host $ldapHost:$ldapPort";
            \OC::$server->getLogger()->error($message, ['app' => Application::APP_ID]);
            return False;
        }
    }

    public function bind() {

        // LDAP variables
        $ds = $this->connect();
        $dn = $this->ldapConfig->ldapAgentName;
        $secret = $this->ldapConfig->ldapAgentPassword;

        // Connecting to LDAP
        if (!ldap_bind($ds,$dn,$secret)) {
            $message = "Unable to bind to LDAP server using credentials $dn > $secret";
            \OC::$server->getLogger()->error($message, ['app' => Application::APP_ID]);
        } else {
            $message = "Bind to LDAP server using credentials $dn";
            \OC::$server->getLogger()->notice($message, ['app' => Application::APP_ID]);
            return $ds;
        }
        // try catch!!!
    }

    public function getLDAPConnection() {
    	return $this->bind();
	}

	public function getLDAPBaseUsers() {
		return $this->ldapConfig->ldapBaseUsers;
	}

	public function getLDAPBaseGroups() {
		return $this->ldapConfig->ldapBaseGroups;
	}
}
