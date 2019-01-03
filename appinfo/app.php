<?php


use OCA\Ldapusermanagement\LDAPUserManager;
use OCA\User_LDAP\UserPluginManager;
use OCP\AppFramework\App;


if (\OCP\App::isEnabled('user_ldap')) {

	$app = new App('user_ldap_extended');
	$container = $app->getContainer();

	$backends = \OC::$server->getUserManager()->getBackends();

	$userManager = $container->query('UserManager');
	$groupManager = $container->query('GroupManager');
	$userSession = $container->query("UserSession");
	$ocConfig = $container->query(\OCP\IConfig::class);

	$ldapConnect = $container->query(\OCA\Ldapusermanagement\LDAPConnect::class);

	$ldapUserManager = new LDAPUserManager($userManager,$groupManager, $userSession, $ldapConnect, $ocConfig);

	$ldapGroupManager = new \OCA\Ldapusermanagement\LDAPGroupManager($groupManager, $userSession, $ldapConnect);

	// register hooks
	#$container->query('OCA\Ldapusermanagement\GroupHooks')->register();

	$userPluginManager = \OC::$server->query('LDAPUserPluginManager');
	$groupPluginManager = \OC::$server->query('LDAPGroupPluginManager');

	$userPluginManager->register($ldapUserManager);
	$groupPluginManager->register($ldapGroupManager);

	$subAdmin = \OC::$server->getGroupManager()->getSubAdmin();

	$subAdmin->listen('\OC\SubAdmin', 'postCreateSubAdmin', function(\OC\User\User $user, \OC\Group\Group $group) use ($ldapGroupManager)  {
		if ($user->getBackendClassName() == "LDAP" and $ldapGroupManager->isLDAPGroup($group->getGID())) {
			$ldapGroupManager->addOwnerToGroup($user->getUID(),$group->getGID());
		}
	});

	$subAdmin->listen('\OC\SubAdmin', 'postDeleteSubAdmin', function(\OC\User\User $user, \OC\Group\Group $group) use ($ldapGroupManager) {
		if ($user->getBackendClassName() == "LDAP" and $ldapGroupManager->isLDAPGroup($group->getGID())) {
			$ldapGroupManager->removeOwnerFromGroup($user->getUID(),$group->getGID());
		}
	});

}
