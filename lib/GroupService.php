<?php
/**
 * @author Alan Tygel <alan@eita.org.br>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

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