<?php
namespace OCA\LdapUserManagement;

class GroupService {

    private $GroupManager;

    public function __construct($GroupManager){
        $this->GroupManager = $GroupManager;
    }

    public static function addUserGroup(\OC\Group\Group $group, \OC\User\User $user) {
    /**
     * add LDAP user to LDAP group
     */
        $ds = GroupService::bindLDAP();
        $dn = "cn=" . $group->getGID() . ",ou=groups,dc=localhost"; //TODO: make configurable
        $entry['memberUid'] = $user->getUID();

        if ( ldap_mod_add ( $ds , $dn , $entry) ) {
            $r = "success";
        } else {
            $r = "fail - $dn - " . print_r($entry, true); // send to log
        }            

         $fid = fopen('/var/www/html/server/apps/ldapusermanagement/log.txt', 'a');
         fwrite($fid, "Add User: " . $user->getUID( ). " to Group: " . $group->getGID() . " >> $r \n");
         fclose($fid);


        \OC::$server->getLogger()->notice(
                "Add User: " . $user->getUID( ). " to Group: " . $group->getGID(),
                array('app' => 'ldapusermanagement'));
    }

    public static function removeUserGroup(\OC\Group\Group $group, \OC\User\User $user) {
    /**
     * remove LDAP user from LDAP group
     */

        $ds = GroupService::bindLDAP();
        $dn = "cn=" . $group->getGID() . ",ou=groups,dc=localhost"; //TODO: make configurable
        $entry['memberUid'] = $user->getUID();

        if ( ldap_mod_del ( $ds , $dn , $entry) ) {
            $r = "success";
        } else {
            $r = "fail - $dn - " . print_r($entry, true); // send to log
        }            


        $fid = fopen('/var/www/html/server/apps/ldapusermanagement/log.txt', 'a');
        fwrite($fid, "Remove User: " . $user->getUID( ). " from Group: " . $group->getGID() . " \n");
        fclose($fid);


        \OC::$server->getLogger()->notice(
                "Remove User: " . $user->getUID( ). " to Group: " . $group->getGID(),
                array('app' => 'ldapusermanagement'));
    }

    public static function createLDAPGroup($groupId) {
    /**
     * create LDAP user
     */
        $ds = GroupService::bindLDAP();

        $entry = array( 
            'objectClass' => array( 'posixGroup' , 'top' ),
            'cn' => $groupId ,
            'gidnumber' => 500, // autoincrement needed?
        );

        $dn = "cn=" . $groupId . ",ou=groups,dc=localhost"; //TODO: make configurable

        if ( ldap_add ( $ds , $dn , $entry) ) {
            $r = "success";
        } else {
            $r = "fail - $dn - " . print_r($entry, true); // send to log
        }            

         $fid = fopen('/var/www/html/server/apps/ldapusermanagement/log.txt', 'w');
         fwrite($fid, "CreateLDAPGroup: " . $groupId . ">> $r \n");
         fclose($fid);

        \OC::$server->getLogger()->notice(
                "CreateLDAPGroup: $groupId >> $r",
                array('app' => 'ldapusermanagement'));
    }

    public static function deleteLDAPGroup(\OC\Group\Group $group){

        $ds = GroupService::bindLDAP();
        $dn = "cn=" . $group->getGID() . ",ou=groups,dc=localhost"; //TODO: make configurable

        if (ldap_delete($ds, $dn))
            $r = "deleted";
        else
            $r = "not deleted";

         $fid = fopen('/var/www/html/server/apps/ldapusermanagement/log.txt', 'a');
         fwrite($fid, "DeleteLDAPGroup: " . $group->getGID( ) . ">> $r \n");
         fclose($fid);


        \OC::$server->getLogger()->notice(
                "DeleteLDAPGrup: " . $group->getGID() . " >> $r",
                array('app' => 'ldapusermanagement'));
    }


    /* ldap functions should all come from LDAP plugin */
    private static function connectLDAP() {
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

    private static function bindLDAP() {

        // LDAP variables
        $ds = GroupService::connectLDAP();
        $dn = 'cn=admin,dc=localhost'; //TODO: get from LDAP plugin
        $secret = 'abb3h5Mv'; //TODO: put in configuration file

        // Connecting to LDAP
        if (!ldap_bind($ds,$dn,$secret)) {
            return FALSE;
        } else {
            return $ds;
        }
    }

    private static function disconnectLDAP($ds) {
        return ldap_unbind($ds);

    }


}