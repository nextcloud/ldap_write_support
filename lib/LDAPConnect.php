<?php

namespace OCA\LdapUserManagement;

class LDAPConnect {

    public static function connect() {
        // TODO: get from LDAP plugin
        $ldaphost = "localhost";
        $ldapport = 389;

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
    }

    public static function bind() {

        // LDAP variables
        $ds = LDAPConnect::connect();
        $dn = 'cn=admin,dc=localhost'; //TODO: get from LDAP plugin
        $secret = 'abb3h5Mv'; //TODO: put in configuration file

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