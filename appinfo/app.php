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

	$ldapUserManager = new LDAPUserManager($userManager,$groupManager, $userSession);

	// register hooks
	$container->query('OCA\Ldapusermanagement\GroupHooks')->register();

	$userPluginManager = \OC::$server->query('LDAPUserPluginManager');
	$groupPluginManager = \OC::$server->query('LDAPGroupPluginManager');

	$userPluginManager->register($ldapUserManager);


	#UserPluginManager::register($ldapUserManager);

} 
// else {
// 		throw new OCSException('The requested group could not be found', \OCP\API::RESPOND_NOT_FOUND);
// }

// $container->query('OCP\INavigationManager')->add(function () use ($container) {
// 	$urlGenerator = $container->query('OCP\IURLGenerator');
// 	$l10n = $container->query('OCP\IL10N');
// 	return [
// 		// the string under which your app will be referenced in Nextcloud
// 		'id' => 'ldapusermanagement',

// 		// sorting weight for the navigation. The higher the number, the higher
// 		// will it be listed in the navigation
// 		'order' => 10,

// 		// the route that will be shown on startup
// 		'href' => $urlGenerator->linkToRoute('ldapusermanagement.page.index'),

// 		// the icon that will be shown in the navigation
// 		// this file needs to exist in img/
// 		'icon' => $urlGenerator->imagePath('ldapusermanagement', 'app.svg'),

// 		// the title of your application. This will be used in the
// 		// navigation or on the settings page of your app
// 		'name' => $l10n->t('Ldap User Management'),
// 	];
// });


// $settings = new \OCA\LdapUserManagement\Settings();

// \OCP\App::registerPersonal('ldapusermanagement', 'personal');
