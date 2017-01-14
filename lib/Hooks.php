<?php

namespace OCA\LdapUserManagement;

class Hooks {

	public static function createLDAPUser_hook($params) {
		if( \OCP\App::isEnabled('ldapusermanagement') ) {
			// create LDAP User
			\OCA\LdapUserManagement\LdapUserManagement::createLDAPUser($params);
		}
	}

	public static function deleteNCUser_hook($params) {
		if( \OCP\App::isEnabled('ldapusermanagement') ) {
			// remove NextCloud User
			$fid = fopen('/var/www/html/server/apps/ldapusermanagement/log.txt', 'a');
			fwrite($fid, "deleteNCUser: " . $params['uid'] . " >> " . $params['password'] . "\n");
			fclose($fid);
		}
	}

	public static function deleteLDAPUser_hook($params) {
		if( \OCP\App::isEnabled('ldapusermanagement') ) {
			// remove LDAP User
			\OCA\LdapUserManagement\LdapUserManagement::deleteLDAPUser($params);
		}
	}


}
