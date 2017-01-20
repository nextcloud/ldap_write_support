<?php

namespace OCA\LdapUserManagement;
use OCP\IUserManager;
use OCA\LdapUserManagement\UserService;
class UserHooks {

    private $userManager;

    public function __construct(IUserManager $userManager){
        $this->userManager = $userManager;
    }

    public function register() {

        $deleteNCUser = function ($user) {            
            /**
             * delete NextCloud user
             */
            // cancel delete LDAP hook
            $cb3 = ['OCA\LdapUserManagement\UserService', 'deleteLDAPUser'];
            $this->userManager->removeListener(null, null, $cb3);

            if ($user->delete())
                $r = "deleted";
            else
                $r = "not deleted";

            \OC::$server->getLogger()->notice(
                    "DeleteNCUser: " . $user->getUID() . " >> $r",
                    array('app' => 'ldapusermanagement'));

        };


        $cb1 = ['OCA\LdapUserManagement\UserService', 'createLDAPUser'];
        $this->userManager->listen('\OC\User', 'preCreateUser', $cb1);

        $cb3 = ['OCA\LdapUserManagement\UserService', 'deleteLDAPUser'];
        $this->userManager->listen('\OC\User', 'preDelete', $cb3);

        $cb2 = ['OCA\LdapUserManagement\UserService', 'deleteNCUser'];
        $this->userManager->listen('\OC\User', 'postCreateUser', $deleteNCUser);



    }

}