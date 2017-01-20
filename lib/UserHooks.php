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

        $cb = ['OCA\LdapUserManagement\UserService', 'createLDAPUser'];
        $this->userManager->listen('\OC\User', 'preCreateUser', $cb);

        $cb = ['OCA\LdapUserManagement\UserService', 'deleteNCUser'];
        $this->userManager->listen('\OC\User', 'postCreateUser', $cb);

        $cb = ['OCA\LdapUserManagement\UserService', 'deleteLDAPUser'];
        $this->userManager->listen('\OC\User', 'preDelete', $cb);

    }

}