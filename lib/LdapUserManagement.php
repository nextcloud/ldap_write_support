<?php

namespace OCA\LdapUserManagement;

class LdapUserManagement {
	/**
	 * register hooks
	 */
	public static function registerHooks() {
		//Listen to delete user signal
		\OCP\Util::connectHook('OC_User', 'pre_createUser', 'OCA\LdapUserManagement\Hooks', 'createLDAPUser_hook');

		\OCP\Util::connectHook('OC_User', 'post_createUser', 'OCA\LdapUserManagement\Hooks', 'deleteNCUser_hook');

		\OCP\Util::connectHook('OC_User', 'pre_deleteUser', 'OCA\LdapUserManagement\Hooks', 'deleteLDAPUser_hook');

	}

	public static function createLDAPUser($params) {
	/**
	 * create LDAP user
	 */
		$ds = LdapUserManagement::connectLDAP();
		if (LdapUserManagement::disconnectLDAP($ds)) {
			$result = "OK";
		} else {
			$result = "Not OK";
		}
		$fid = fopen('/var/www/html/server/apps/ldapusermanagement/log.txt', 'w');
		fwrite($fid, "createLDAPUser: " . $params['uid'] . " >> " . $params['password'] . " >> $result \n");
		fclose($fid);
	}

	public static function deleteLDAPUser($params) {
	/**
	 * create LDAP user
	 */
		$uid = $params['uid']; // não é esso o parametro!!!
		$ds = LdapUserManagement::connectLDAP();


		$dn = "dc=localhost";
		$filter="(&(|(objectclass=inetOrgPerson)(objectclass=posixAccount)(objectclass=top))(|(|(mailPrimaryAddress=$uid)(mail=$uid))(|(dc=$uid)(o=$uid)(objectClass=$uid))))";
		$justthese = array("ou", "sn", "givenname", "mail");
    	$sr=ldap_search($ds, $dn, $filter, $justthese);
		$info = ldap_get_entries($ds, $sr);

		if ($info['count'] == 1) {
			$result = "User Exist";
		} else {
			$result = "User not found";
		}

		LdapUserManagement::disconnectLDAP($ds);

		$fid = fopen('/var/www/html/server/apps/ldapusermanagement/log.txt', 'w');
		fwrite($fid, "deleteLDAPUser: " . $params['uid'] . " >> " . $params['password'] . " >> $result \n");
		fclose($fid);
	}


	private static function connectLDAP() {
		# code...
		// LDAP variables
		$ldaphost = "localhost";  // your ldap servers
		$ldapport = 389;                 // your ldap server's port number

		// Connecting to LDAP
		$ds = $ldapconn = ldap_connect($ldaphost, $ldapport)
		          or die("Could not connect to $ldaphost");

		if ($ds) {
		    // bind with appropriate dn to give update access
		    // $r = ldap_bind($ds, "cn=admin, dc=localhost", "abb3h5Mv");

			// $dn = "dc=localhost";
			// $filter="(objectClass=inetOrgPerson)";
			// $justthese = array("ou", "sn", "givenname", "mail");
		 //    $sr=ldap_search($ds, $dn, $filter, $justthese);

			// $info = ldap_get_entries($ds, $sr);
		 //    ldap_close($ds);

			// return $info["count"]." entries returned\n";
			return $ds;

		} else {
		    return "Unable to connect to LDAP server";
		}
	}

	private static function disconnectLDAP($ds) {
		return ldap_unbind($ds);

	}

}
