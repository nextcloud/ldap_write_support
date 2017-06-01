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

use OC\Accounts;

class UserService {

    private $userManager;
    private $accountManager;

    public function __construct($userManager , $accountManager){
        $this->userManager = $userManager;
        $this->accountManager = $accountManager;
    }

    public static function createLDAPUser($uid, $password) {
    /**
     * create LDAP user
     */
        $ds = LDAPConnect::bind();

        $uid_number = 2010;

        $entry = array( 
            'o' => $uid ,
            'objectClass' => array( 'inetOrgPerson', 'posixAccount', 'top'),
            'cn' => $uid ,
            'gidnumber' => 501,
            'homedirectory' => 'x', // ignored by nextcloud
            'mail' => 'x@x.com',
            'sn' => $uid ,
            'uid' => $uid , // mandatory
            'uidnumber' => $uid_number, // mandatory
            'userpassword' => $password ,
            'displayName' => $uid,
            'street' => "address",
        );
        // when LDAP user is deleted, user folder remains there

        // $dn = "cn=" . $uid . "," . \OCP\Config::getAppValue('ldapusermanagement','userbase','');
        $dn = "cn=" . $uid . "," . \OCP\Config::getAppValue('user_ldap','ldap_base_users','');        

        if (!ldap_add ( $ds , $dn , $entry)) {
            $message = "Unable to create LDAP user '$uid' ($dn)";
            \OC::$server->getLogger()->error($message, array('app' => 'ldapusermanagement'));
        } else {
            $message = "Create LDAP user '$uid' ($dn)";
            \OC::$server->getLogger()->notice($message, array('app' => 'ldapusermanagement'));
        }
    }

    public static function deleteLDAPUser($user){

        $ds = LDAPConnect::bind();
        $dn = "cn=" . $user->getUID() . "," . \OCP\Config::getAppValue('user_ldap','ldap_base_users','');

        if (!ldap_delete ( $ds , $dn )) {
            $message = "Unable to delete LDAP user " . $user->getUID();
            \OC::$server->getLogger()->error($message, array('app' => 'ldapusermanagement'));
        } else {
            $message = "Delete LDAP user (isDeleted): " . $user->getUID();
            \OC::$server->getLogger()->notice($message, array('app' => 'ldapusermanagement'));

            \OCP\Config::setUserValue($user->getUID(), 'user_ldap', 'isDeleted', 1);
        }
    }

    public static function changeLDAPUserAttributes ( \Symfony\Component\EventDispatcher\GenericEvent $event ){

        $user = $event->getSubject();

        $ds = LDAPConnect::bind();
        $dn = "cn=" . $user->getUID() . "," . \OCP\Config::getAppValue('user_ldap','ldap_base_users','');

        $accountManager = new \OC\Accounts\AccountManager (                 
                \OC::$server->getDatabaseConnection(),
                \OC::$server->getEventDispatcher(),
                \OC::$server->getJobList()
                 );

        $userData = $accountManager->getUser( $user );

        $entry = NULL;
        $entry['mail'] = $userData['email']['value'];
        $entry['displayName'] = $userData['displayname']['value'];
        if ($userData['address']['value']) 
            $entry['street'] = $userData['address']['value'];

        if (!ldap_mod_replace ( $ds , $dn , $entry)) {
            $message = "Unable to modify user attributes " . $entry['mail'] . " and " . $entry['displayName'] . " and " . $userData['address']['value'];
            \OC::$server->getLogger()->error($message, array('app' => 'ldapusermanagement'));
        } else {
            $message = "Modify user attributes " . $entry['mail'] . " and " . $entry['displayName'] . " and " . $userData['address']['value'];
            \OC::$server->getLogger()->notice($message, array('app' => 'ldapusermanagement'));
        }
    }

/*
    public static function changeLDAPUser ( \OC\User\User $user, string $feature, string $value , string $oldvalue ){

        $ds = LDAPConnect::bind();
        $dn = "cn=" . $user->getUID() . "," . \OCP\Config::getAppValue('user_ldap','ldap_base_users','');

        $message = "Unable to modify user attributes " . $feature . " and " . $value ;
        \OC::$server->getLogger()->error($message, array('app' => 'ldapusermanagement'));


        $entry = NULL;
        $entry['mail'] = $userData['email']['value'];
        $entry['displayName'] = $userData['displayname']['value'];
        if ($userData['address']['value']) 
            $entry['street'] = $userData['address']['value'];

        if (!ldap_mod_replace ( $ds , $dn , $entry)) {
            $message = "Unable to modify user attributes " . $entry['mail'] . " and " . $entry['displayName'] . " and " . $userData['address']['value'];
            \OC::$server->getLogger()->error($message, array('app' => 'ldapusermanagement'));
        } else {
            $message = "Modify user attributes " . $entry['mail'] . " and " . $entry['displayName'] . " and " . $userData['address']['value'];
            \OC::$server->getLogger()->notice($message, array('app' => 'ldapusermanagement'));
        }
    }
*/    
}
