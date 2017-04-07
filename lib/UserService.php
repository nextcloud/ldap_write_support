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

	$uid_number = 2010;

        $entry = array( 
            'o' => $uid ,
            'objectClass' => array( 'inetOrgPerson', 'posixAccount', 'top'),
            'cn' => $uid ,
            'gidnumber' => 500,
            'homedirectory' => 'x', // ignored by nextcloud
            'mail' => 'x@x.com',
            'sn' => $uid ,
            'uid' => $uid , // mandatory
            'uidnumber' => $uid_number, // mandatory
            'userpassword' => $password ,
            'displayName' => $uid,
        );
        // when LDAP user is deleted, user folder remains there

        $dn = "cn=" . $uid . "," . \OCP\Config::getAppValue('ldapusermanagement','userbase','');

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
        $dn = "cn=" . $user->getUID() . "," . \OCP\Config::getAppValue('ldapusermanagement','userbase','');

        if (!ldap_delete ( $ds , $dn )) {
            $message = "Unable to delete LDAP user " . $user->getUID();
            \OC::$server->getLogger()->error($message, array('app' => 'ldapusermanagement'));
        } else {
            $message = "Delete LDAP user (isDeleted): " . $user->getUID();
            \OC::$server->getLogger()->notice($message, array('app' => 'ldapusermanagement'));

            // \OCP\Config::setUserValue($user->getUID(), 'user_ldap', 'isDeleted', 1);
        }
    }


    public static function changeLDAPUser( $user ){
    /* this hook was supposed to get array( $user, $modified_feature, $value) as input, but only $user is comming */

        $ds = LDAPConnect::bind();
        $dn = "cn=" . $user->getUID() . "," . \OCP\Config::getAppValue('ldapusermanagement','userbase','');

        $entry = NULL;
        $entry['mail'] = $user->getEMailAddress();
        $entry['displayName'] = $user->getDisplayName();

        if (!ldap_mod_replace ( $ds , $dn , $entry)) {
            $message = "Unable to modify user attributes " . $user->getEMailAddress( ). " and " . $user->getDisplayName();
            \OC::$server->getLogger()->error($message, array('app' => 'ldapusermanagement'));
        } else {
            $message = "Modify user attributes " . $user->getEMailAddress( ). " and " . $user->getDisplayName();
            \OC::$server->getLogger()->notice($message, array('app' => 'ldapusermanagement'));
        }
    }
}
