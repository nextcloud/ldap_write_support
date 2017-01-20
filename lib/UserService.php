<?php
namespace OCA\LdapUserManagement;

class UserService {

    private $userManager;

    public function __construct($userManager){
        $this->userManager = $userManager;
    }

    public static function createLDAPUser($uid, $password) {
    /**
     * create LDAP user
     */
        $ds = UserService::bindLDAP();

        $entry = array( 
            'o' => $uid ,
            'objectClass' => array( 'inetOrgPerson', 'posixAccount', 'top'),
            'cn' => $uid ,
            'gidnumber' => 500,
            'homedirectory' => '', // ignored by nextcloud
            'mail' => '',
            'sn' => $uid ,
            'uid' => $uid , // mandatory
            'uidnumber' => 1010, // mandatory - verify is autoincrement is needed
            'userpassword' => $password ,
        );
        // when LDAP user is deleted, user folder remains there

        $dn = "cn=" . $uid . ",ou=users,dc=localhost"; //TODO: make configurable

        if ( ldap_add ( $ds , $dn , $entry) ) {
            $r = "success";
        } else {
            $r = "fail - $dn - " . print_r($entry, true); // send to log
        }            

        \OC::$server->getLogger()->notice(
                "CreateLDAPUser: $uid >> $password >> $r",
                array('app' => 'ldapusermanagement'));
    }

    public function deleteNCUser($user) {            
    /**
     * delete NextCloud user
     */
        if ($user->delete())
            $r = "deleted";
        else
            $r = "not deleted";

        \OC::$server->getLogger()->notice(
                "DeleteNCUser: " . $user->getUID() . " >> $r",
                array('app' => 'ldapusermanagement'));

        // cancel delete LDAP hook
        $cb3 = ['OCA\LdapUserManagement\UserService', 'deleteLDAPUser'];
        $this->userManager->removeListener(null, null, $cb3);

    }

    public static function deleteLDAPUser($user){

        $ds = UserService::bindLDAP();
        $dn = "cn=" . $user->getUID() . ",ou=users,dc=localhost"; //TODO: make configurable

        if (ldap_delete($ds, $dn))
            $r = "deleted";
        else
            $r = "not deleted";

        \OC::$server->getLogger()->notice(
                "DeleteLDAPUser: " . $user->getUID() . " >> $r",
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
        $ds = UserService::connectLDAP();
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
