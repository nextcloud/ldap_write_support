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

namespace OCA\Ldapusermanagement;

use OCA\Ldapusermanagement;
use OCP\IConfig;

class LDAPConnect {

    public static function connect() {
        // TODO: get from LDAP plugin ?
        $ldaphost  = \OCP\Config::getAppValue('ldapusermanagement','host','');
        $ldapport  = \OCP\Config::getAppValue('ldapusermanagement','port','');

        // Connecting to LDAP - TODO: connect directly via LDAP plugin
        $ds = $ldapconn = ldap_connect($ldaphost, $ldapport)
                  or die("Could not connect to $ldaphost");

        if ($ds) {
            // set LDAP config to work with version 3
            ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
            $message = "Connected to LDAP host $ldaphost:$ldapport";
            \OC::$server->getLogger()->notice($message, array('app' => 'ldapusermanagement'));
            return $ds;
        } else {
            $message = "Unable to connect to LDAP host $ldaphost:$ldapport";
            \OC::$server->getLogger()->error($message, array('app' => 'ldapusermanagement'));
            return False;
        }
    }

    public static function bind() {

        // LDAP variables
        $ds = LDAPConnect::connect();
        $dn = \OCP\Config::getAppValue('ldapusermanagement','dn','');
        $secret = \OCP\Config::getAppValue('ldapusermanagement','password','');

        // Connecting to LDAP
        if (!ldap_bind($ds,$dn,$secret)) {
            $message = "Unable to bind to LDAP server using credentials $dn > $secret";
            \OC::$server->getLogger()->error($message, array('app' => 'ldapusermanagement'));
        } else {
            $message = "Bind to LDAP server using credentials $dn";
            \OC::$server->getLogger()->notice($message, array('app' => 'ldapusermanagement'));
            return $ds;
        }
        // try catch!!!
    }

    public static function disconnect($ds) {
        return ldap_unbind($ds);

    }
}