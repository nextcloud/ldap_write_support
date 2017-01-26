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
        $ds = LDAPConnect::bind();

        $entry = array( 
            'o' => $uid ,
            'objectClass' => array( 'inetOrgPerson', 'posixAccount', 'top'),
            'cn' => $uid ,
            'gidnumber' => 500,
            'homedirectory' => 'x', // ignored by nextcloud
            'mail' => 'x@x.com',
            'sn' => $uid ,
            'uid' => $uid , // mandatory
            'uidnumber' => 1010, // mandatory - verify is autoincrement is needed
            'userpassword' => $password ,
            'displayName' => $uid,
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

        $ds = LDAPConnect::bind();
        $dn = "cn=" . $user->getUID() . ",ou=users,dc=localhost"; //TODO: make configurable

        if (ldap_delete($ds, $dn))
            $r = "deleted";
        else
            $r = "not deleted";

        \OC::$server->getLogger()->notice(
                "DeleteLDAPUser: " . $user->getUID() . " >> $r",
                array('app' => 'ldapusermanagement'));
    }

}
