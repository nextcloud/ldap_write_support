<?php

namespace OCA\LdapUserManagement;
use OCP\IGroupManager;

class GroupHooks {

    private $GroupManager;

    public function __construct(IGroupManager $GroupManager){
        $this->GroupManager = $GroupManager;
    }

    public function register() {

        $deleteNCGroup = function (\OC\Group\Group $group) {            
            /**
             * delete NextCloud group
             */
            // cancel delete LDAP hook
            
            $this->GroupManager->removeListener(null, null, ['OCA\LdapUserManagement\GroupService', 'deleteLDAPGroup']);

            if ($group->delete())
                $r = "deleted";
            else
                $r = "not deleted";

         $fid = fopen('/var/www/html/server/apps/ldapusermanagement/log.txt', 'a');
         fwrite($fid, "DeleteNCGroup: " . $group->getGID( ) . ">> $r \n");
         fclose($fid);


            \OC::$server->getLogger()->notice(
                    "DeleteNCGroup: " . $group->getGID() . " >> $r",
                    array('app' => 'ldapusermanagement'));

        };

        $this->GroupManager->listen('\OC\Group', 'preAddUser', ['OCA\LdapUserManagement\GroupService', 'addUserGroup']);

        $this->GroupManager->listen('\OC\Group', 'preRemoveUser', ['OCA\LdapUserManagement\GroupService', 'removeUserGroup']);

        $this->GroupManager->listen('\OC\Group', 'preCreate', ['OCA\LdapUserManagement\GroupService', 'createLDAPGroup']);

        $this->GroupManager->listen('\OC\Group', 'preDelete', ['OCA\LdapUserManagement\GroupService', 'deleteLDAPGroup']);

        $this->GroupManager->listen('\OC\Group', 'postCreate', $deleteNCGroup);



    }

}