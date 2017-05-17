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
use OCP\IGroupManager;

class GroupHooks {

    private $GroupManager;

    public function __construct(IGroupManager $GroupManager){
        $this->groupManager = $GroupManager;
    }

    public function register() {

        $deleteNCGroup = function (\OC\Group\Group $group) {            
            /**
             * delete NextCloud group
             */

            // cancel delete LDAP hook
            $this->groupManager->removeListener(null, null, ['OCA\Ldapusermanagement\GroupService', 'deleteLDAPGroup']);

            if (!$group->delete()){
                $message = "Unable to delete NextCloud group " . $group->getGID();
                \OC::$server->getLogger()->error($message, array('app' => 'ldapusermanagement'));
            } else {
                $message = "Delete NextCloud group " . $group->getGID();
                \OC::$server->getLogger()->notice($message, array('app' => 'ldapusermanagement'));
            }
        };

        $this->groupManager->listen('\OC\Group', 'preAddUser', ['OCA\Ldapusermanagement\GroupService', 'addUserGroup']);

        $this->groupManager->listen('\OC\Group', 'preRemoveUser', ['OCA\Ldapusermanagement\GroupService', 'removeUserGroup']);

        $this->groupManager->listen('\OC\Group', 'preDelete', ['OCA\Ldapusermanagement\GroupService', 'deleteLDAPGroup']);

        $this->groupManager->listen('\OC\Group', 'preCreate', ['OCA\Ldapusermanagement\GroupService', 'createLDAPGroup']);

        $this->groupManager->listen('\OC\Group', 'postCreate', $deleteNCGroup);

    }

}
