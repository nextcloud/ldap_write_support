<?php

namespace OCA\LdapUserManagement;

// use \OCA\LdapUserManagement\Service\UserService;

class LdapUserManagement {

	// private $userService;

	// public function __construct(UserService $service){
 //        $this->userService = $service;
 //    }

	/**
	 * register hooks
	 */
	// public static function registerHooks() {
	// 	//Listen to delete user signal
	// 	\OCP\Util::connectHook('OC_User', 'pre_createUser', 'OCA\LdapUserManagement\Hooks', 'createLDAPUser_hook');

	// 	\OCP\Util::connectHook('OC_User', 'post_createUser', 'OCA\LdapUserManagement\Hooks', 'deleteNCUser_hook');

	// 	\OCP\Util::connectHook('OC_User', 'pre_deleteUser', 'OCA\LdapUserManagement\Hooks', 'deleteLDAPUser_hook');

	// 	\OCP\Util::connectHook('OC_Group', 'preCreate', 'OCA\LdapUserManagement\Hooks', 'addUserGroupLDAP_hook');

	// }

	// public static function createLDAPUser($params) {
	// /**
	//  * create LDAP user
	//  */
	// 	$ds = LdapUserManagement::bindLDAP();

	// 	$entry = array(	
	// 		'o' => $params['uid'] ,
	// 		'objectClass' => array( 'inetOrgPerson', 'posixAccount', 'top'),
	// 		'cn' => $params['uid'] ,
	// 		'gidnumber' => 500,
	// 		'homedirectory' => '', // ignored by nextcloud
	// 		'mail' => '',
	// 		'sn' => $params['uid'] ,
	// 		'uid' => $params['uid'] , // mandatory
	// 		'uidnumber' => 1010, // mandatory - verify is autoincrement is needed
	// 		'userpassword' => $params['password'] ,
	// 	);
	// 	// when LDAP user is deleted, user folder remains there

	// 	$dn = "cn=" . $params['uid'] . ",ou=users,dc=localhost"; //TODO: make configurable

	// 	$fid = fopen('/var/www/html/server/apps/ldapusermanagement/log.txt', 'w');
	// 	fwrite($fid, "createLDAPUser: " . $params['uid'] . " >> " . $params['password'] . " >> \n");
	// 	fclose($fid);

 // 		if ( ldap_add ( $ds , $dn , $entry) ) {
	// 		return True; 			
 // 		} else {
 // 			return "fail - $dn - " . print_r($entry, true); // send to log
 // 		}		     

	// }

	// public static function deleteNCUser($params) {

	// 	$us = new UserService;
	// 	$r = $us->delete($params['uid']);

	// 	if ($r)
	// 		$return = "deleted";
	// 	else
	// 		$return = "failed";

	// 	$fid = fopen('/var/www/html/server/apps/ldapusermanagement/log.txt', 'a');
	// 	fwrite($fid, "deleteNCUser: $return ". var_export($r,true) . "  --  ".  $params['uid'] . " >> " . $params['password'] . "\n");
	// 	fclose($fid);
	// }
	// public static function deleteLDAPUser($params) {
	// /**
	//  * delete LDAP user
	//  */
	// 	$uid = $params['uid'];
	// 	$ds = LdapUserManagement::connectLDAP();


	// 	$dn = "dc=localhost";
	// 	$filter="(&(|(objectclass=inetOrgPerson)(objectclass=posixAccount)(objectclass=top))(|(|(mailPrimaryAddress=$uid)(mail=$uid))(|(dc=$uid)(o=$uid)(objectClass=$uid))))";
	// 	$justthese = array("ou", "sn", "givenname", "mail");
 //    	$sr=ldap_search($ds, $dn, $filter, $justthese);
	// 	$info = ldap_get_entries($ds, $sr);

	// 	if ($info['count'] == 1) {
	// 		$result = "User Exist";
	// 	} else {
	// 		$result = "User not found";
	// 	}

	// 	LdapUserManagement::disconnectLDAP($ds);

	// 	$fid = fopen('/var/www/html/server/apps/ldapusermanagement/log.txt', 'w');
	// 	fwrite($fid, "deleteLDAPUser: " . print_r($params, true) . " >> $result \n");
	// 	fclose($fid);
	// }

	// public static function addUserGroupLDAP($params) {
	// /**
	//  * create LDAP user
	//  */
	// 	$result = 'xx';
	// 	$fid = fopen('/var/www/html/server/apps/ldapusermanagement/log.txt', 'w');
	// 	fwrite($fid, "addUserGroupLDAP: " . $params['group'] . " >> " . $params['user'] . " >> $result \n");
	// 	fclose($fid);
	// }

	// private static function connectLDAP() {
	// 	// TODO: get from LDAP plugin
	// 	$ldaphost = "localhost";
	// 	$ldapport = 389;

	// 	// Connecting to LDAP - TODO: connect directly via LDAP plugin
	// 	$ds = $ldapconn = ldap_connect($ldaphost, $ldapport)
	// 	          or die("Could not connect to $ldaphost");

	// 	if ($ds) {
	// 		// set LDAP config to work with version 3
	// 		ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
	// 		return $ds;
	// 	} else {
	// 	    return "Unable to connect to LDAP server";
	// 	}
	// }

	// private static function bindLDAP() {

	// 	// LDAP variables
	// 	$ds = LdapUserManagement::connectLDAP();
	// 	$dn = 'cn=admin,dc=localhost'; //TODO: get from LDAP plugin
	// 	$secret = 'abb3h5Mv'; //TODO: put in configuration file

	// 	// Connecting to LDAP
	// 	if (!ldap_bind($ds,$dn,$secret)) {
	// 		return FALSE;
	// 	} else {
	// 		return $ds;
	// 	}
	// }

	// private static function disconnectLDAP($ds) {
	// 	return ldap_unbind($ds);

	// }

}
