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
use OCA\User_LDAP\LDAPProvider;
use OCP\AppFramework\IAppContainer;
use OCP\IImage;
use OCP\IUserManager;


class UserHooks {

    private $userManager;
    private $ldapUserManager;

    public function __construct( IUserManager $UserManager ) {
        $this->userManager = $UserManager;
    }

    public function register($ldapUserManager) {
    	$this->ldapUserManager = $ldapUserManager;

        $deleteNCUser = function ( $user ) {            
            /**
             * delete NextCloud user
             */
            // cancel delete LDAP hook
            $cb3 = ['OCA\Ldapusermanagement\LDAPUserManagerDeprecated', 'deleteLDAPUser'];
            $this->userManager->removeListener(null, null, $cb3);

            if (!$user->delete()){
                $message = "Unable to delete NextCloud user " . $user->getUID();
                \OC::$server->getLogger()->error($message, array('app' => 'ldapusermanagement'));
            } else {
                $message = "Delete NextCloud user " . $user->getUID();
                \OC::$server->getLogger()->notice($message, array('app' => 'ldapusermanagement'));
            }
        };

        //$cb1 = ['OCA\Ldapusermanagement\LDAPUserManagerDeprecated', 'createLDAPUser'];
        //$this->userManager->listen('\OC\User', 'preCreateUser', $cb1);

        //$cb3 = ['OCA\Ldapusermanagement\LDAPUserManagerDeprecated', 'deleteLDAPUser'];
        //$this->userManager->listen('\OC\User', 'preDelete', $cb3);

        /* this hook listens only to email and display name changes */
        // $cb4 = ['OCA\Ldapusermanagement\UserService', 'changeLDAPUser'];
        // $this->userManager->listen('\OC\User', 'changeUser', $cb4);

        /* this pseudo-hook listens every change in user attributes. */
        //$cb5 = ['OCA\Ldapusermanagement\LDAPUserManagerDeprecated', 'changeLDAPUserAttributes'];
        //$eventDispatcher = \OC::$server->getEventDispatcher();
        //$eventDispatcher->addListener('OC\AccountManager::userUpdated', $cb5);

        /* disable deleting NC user in order to make email and displayName fields available for LDAP Users. However, new LDAP users shows duplicated in NC user list */
        //$cb2 = ['OCA\Ldapusermanagement\LDAPUserManagerDeprecated', 'deleteNCUser'];
        //$this->userManager->listen('\OC\User', 'postCreateUser', $deleteNCUser);

		/* listen to avatar change */

		$this->userManager->listen('\OC\User', 'changeUser', array($this, 'changeUserHook'));
    }

	public function changeUserHook($user, $feature) {
		if ($feature == 'avatar') {
			$this->ldapUserManager->changeAvatar($user);
		}
	}


}
