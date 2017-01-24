<?php

namespace OCA\LdapUserManagement;
use OCP\IGroupManager;

class GroupHooks {

    private $GroupManager;

    public function __construct(IGroupManager $GroupManager){
        $this->GroupManager = $GroupManager;
    }

    public function register() {

        $this->GroupManager->listen('\OC\Group', 'preAddUser', ['OCA\LdapUserManagement\GroupService', 'addUserGroup']);

        $this->GroupManager->listen('\OC\Group', 'preRemoveUser', ['OCA\LdapUserManagement\GroupService', 'removeUserGroup']);


    }

}