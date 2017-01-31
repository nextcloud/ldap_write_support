<?php

namespace OCA\Ldapusermanagement;

use OCA\Ldapusermanagement;
use OCP\IConfig;

class LDAPConnect {

    public static function connect() {
        // TODO: get from LDAP plugin
        $ldaphost  = \OCP\Config::getAppValue('ldapusermanagement','host','');
        $ldapport  = \OCP\Config::getAppValue('ldapusermanagement','port','');

        // Connecting to LDAP - TODO: connect directly via LDAP plugin
        $ds = $ldapconn = ldap_connect($ldaphost, $ldapport)
                  or die("Could not connect to $ldaphost");

        if ($ds) {
            // set LDAP config to work with version 3
            ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
            return $ds;
        } else {
            return "Unable to connect to LDAP server";
        }
 
        $fid = fopen('/var/www/html/server/apps/ldapusermanagement/log.txt', 'w');
        fwrite($fid, "LDAP Connect: $host \n");
        fclose($fid);


    }

    public static function bind() {

        // LDAP variables
        $ds = LDAPConnect::connect();
        $dn = \OCP\Config::getAppValue('ldapusermanagement','dn','');
        $secret = \OCP\Config::getAppValue('ldapusermanagement','password','');

        // Connecting to LDAP
        if (!ldap_bind($ds,$dn,$secret)) {
            return FALSE;
        } else {
            return $ds;
        }
    }

    public static function disconnect($ds) {
        return ldap_unbind($ds);

    }
}