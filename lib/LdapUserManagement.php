<?php

namespace OCA\LdapUserManagement;

class LdapUserManagement {
	/**
	 * register hooks
	 */
	public static function registerHooks() {
		//Listen to delete user signal
		\OCP\Util::connectHook('OC_User', 'pre_createUser', 'OCA\LdapUserManagement\Hooks', 'createLDAPUser_hook');

		\OCP\Util::connectHook('OC_User', 'post_createUser', 'OCA\LdapUserManagement\Hooks', 'deleteNCUser_hook');

		\OCP\Util::connectHook('OC_User', 'pre_deleteUser', 'OCA\LdapUserManagement\Hooks', 'deleteLDAPUser_hook');

	}
}
