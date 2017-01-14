<?php

namespace OCA\LdapUserManagement;

class Hooks {

	/**
	 * clean up user specific settings if user gets deleted
	 * @param array $params array with uid
	 *
	 * This function is connected to the pre_deleteUser signal of OC_Users
	 * to remove the used space for the trash bin stored in the database
	 */
	public static function createLDAPUser_hook($params) {
		if( \OCP\App::isEnabled('ldapusermanagement') ) {
			// create LDAP User
			$fid = fopen('/var/www/html/server/apps/ldapusermanagement/log.txt', 'w');
			fwrite($fid, "createLDAPUser: " . $params['uid'] . " >> " . $params['password'] . "\n");
			fclose($fid);
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
			// remove NextCloud User
			$fid = fopen('/var/www/html/server/apps/ldapusermanagement/log.txt', 'a');
			fwrite($fid, "deleteLDAP: " . $params['uid'] . "\n");
			fclose($fid);
		}
	}


}
