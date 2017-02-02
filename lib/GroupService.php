<?php
namespace OCA\Ldapusermanagement;

use OCP\IConfig;
use OCP\IL10N;

class GroupService {

    private $GroupManager;

    public function __construct($GroupManager){
        $this->GroupManager = $GroupManager;
    }

    public static function addUserGroup(\OC\Group\Group $group, \OC\User\User $user) {
    /**
     * add LDAP user to LDAP group
     */
        $ds = LDAPConnect::bind();
        $dn = "cn=" . $group->getGID() . "," . \OCP\Config::getAppValue('ldapusermanagement','groupbase','');
        $entry['memberuid'] = $user->getUID();

        if (!ldap_mod_add ( $ds , $dn , $entry)) {
            $message = "Unable to add user " . $user->getUID( ). " to group " . $group->getGID();
            \OC::$server->getLogger()->error($message, array('app' => 'ldapusermanagement'));
        } else {
            $message = "Add user: " . $user->getUID( ). " to group: " . $group->getGID();
            \OC::$server->getLogger()->notice($message, array('app' => 'ldapusermanagement'));
        }
    }

    public static function removeUserGroup(\OC\Group\Group $group, \OC\User\User $user) {
    /**
     * remove LDAP user from LDAP group
     */

        $ds = LDAPConnect::bind();
        $dn = "cn=" . $group->getGID() . "," . \OCP\Config::getAppValue('ldapusermanagement','groupbase','');

        $entry['memberuid'] = $user->getUID();

        if ( !ldap_mod_del ( $ds , $dn , $entry) ) {
            $message = "Unable to remove user: " . $user->getUID( ). " from group: " . $group->getGID();
            \OC::$server->getLogger()->error($message, array('app' => 'ldapusermanagement'));
        } else {
            $message = "Remove user: " . $user->getUID( ). " from group: " . $group->getGID();
            \OC::$server->getLogger()->notice($message, array('app' => 'ldapusermanagement'));
        }            
    }

    public static function createLDAPGroup($groupId) {
    /**
     * create LDAP user
     */
        $ds = LDAPConnect::bind();

        $entry = array( 
            'objectClass' => array( 'posixGroup' , 'top' ),
            'cn' => $groupId ,
            'gidnumber' => 500, // autoincrement needed?
        );

        $dn = "cn=" . $groupId . ",ou=groups,dc=localhost"; //TODO: make configurable

        if ( !ldap_add ( $ds , $dn , $entry) ) {
            $message = "Unable to create LDAP Group: " . $groupId;
            \OC::$server->getLogger()->error($message, array('app' => 'ldapusermanagement'));
        } else {
            $message = "Create LDAP Group: " . $groupId;
            \OC::$server->getLogger()->notice($message, array('app' => 'ldapusermanagement'));
        }
    }

    public static function deleteLDAPGroup(\OC\Group\Group $group){

        $ds = LDAPConnect::bind();
        $dn = "cn=" . $group->getGID() . ",ou=groups,dc=localhost"; //TODO: make configurable

        if ( !ldap_delete($ds, $dn) ) {
            $message = "Unable to delete LDAP Group: " . $group->getGID() ;
            \OC::$server->getLogger()->error($message, array('app' => 'ldapusermanagement'));
        } else {
            $message = "Delete LDAP Group: " . $group->getGID() ;
            \OC::$server->getLogger()->notice($message, array('app' => 'ldapusermanagement'));
        }
    }
}