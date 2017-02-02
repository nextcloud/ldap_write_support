<?php
namespace OCA\Ldapusermanagement;

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

        $dn = "cn=" . $uid . "," . \OCP\Config::getAppValue('ldapusermanagement','userbase','');

        if (!ldap_add ( $ds , $dn , $entry)) {
            $message = "Unable to create LDAP user " . $uid;
            \OC::$server->getLogger()->error($message, array('app' => 'ldapusermanagement'));
        } else {
            $message = "Create LDAP user: " . $uid;
            \OC::$server->getLogger()->notice($message, array('app' => 'ldapusermanagement'));
        }
    }

    public static function deleteLDAPUser($user){

        $ds = LDAPConnect::bind();
        $dn = "cn=" . $uid . "," . \OCP\Config::getAppValue('ldapusermanagement','userbase','');

        if (!ldap_delete ( $ds , $dn )) {
            $message = "Unable to delete LDAP user " . $user->getUID();
            \OC::$server->getLogger()->error($message, array('app' => 'ldapusermanagement'));
        } else {
            $message = "Delete LDAP user: " . $user->getUID();
            \OC::$server->getLogger()->notice($message, array('app' => 'ldapusermanagement'));
        }
    }

}
