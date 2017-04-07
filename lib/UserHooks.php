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
use OCP\IUserManager;

class UserHooks {

    private $userManager;

    public function __construct(IUserManager $UserManager){
        $this->userManager = $UserManager;
    }

    public function register() {

        $deleteNCUser = function ( $user ) {            
            /**
             * delete NextCloud user
             */
            // cancel delete LDAP hook
            $cb3 = ['OCA\Ldapusermanagement\UserService', 'deleteLDAPUser'];
            $this->userManager->removeListener(null, null, $cb3);

            if (!$user->delete()){
                $message = "Unable to delete NextCloud user " . $user->getUID();
                \OC::$server->getLogger()->error($message, array('app' => 'ldapusermanagement'));
            } else {
                $message = "Delete NextCloud user " . $user->getUID();
                \OC::$server->getLogger()->notice($message, array('app' => 'ldapusermanagement'));
            }
        };

        $cb1 = ['OCA\Ldapusermanagement\UserService', 'createLDAPUser'];
        $this->userManager->listen('\OC\User', 'preCreateUser', $cb1);

        $cb3 = ['OCA\Ldapusermanagement\UserService', 'deleteLDAPUser'];
        $this->userManager->listen('\OC\User', 'preDelete', $cb3);

        $cb4 = ['OCA\Ldapusermanagement\UserService', 'changeLDAPUser'];
        $this->userManager->listen('\OC\User', 'changeUser', $cb4);

        /* disable deleting NC user in order to make email and displayName fields available for LDAP Users. However, new LDAP users shows duplicated in NC user list */
        // $cb2 = ['OCA\Ldapusermanagement\UserService', 'deleteNCUser'];
        // $this->userManager->listen('\OC\User', 'postCreateUser', $deleteNCUser);

    }

}