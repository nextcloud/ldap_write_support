<?php


use OCP\AppFramework\App;
use OCP\AppFramework\OCS\OCSException;

$helper = new \OCA\User_LDAP\Helper(\OC::$server->getConfig());
$configPrefixes = $helper->getServerConfigurationPrefixes(true);
if(count($configPrefixes) > 0) {
	$ldapWrapper = new OCA\User_LDAP\LDAP();
	$ocConfig = \OC::$server->getConfig();
	$notificationManager = \OC::$server->getNotificationManager();
	$notificationManager->registerNotifier(function() {
		return new \OCA\User_LDAP\Notification\Notifier(
			\OC::$server->getL10NFactory()
		);
	}, function() {
		$l = \OC::$server->getL10N('user_ldap');
		return [
			'id' => 'user_ldap',
			'name' => $l->t('LDAP user and group backend'),
		];
	});

	$userBackend  = new OCA\ldapusermanagement\lib\User_Proxy_Edit(
		$configPrefixes, $ldapWrapper, $ocConfig, $notificationManager
	);
	// register user backend
	OC_User::useBackend($userBackend);
}




if (\OCP\App::isEnabled('user_ldap')) {

	$app = new App('ldapusermanagement');
	$container = $app->getContainer();
	// register hooks
	$container->query('OCA\Ldapusermanagement\UserHooks')->register();
	$container->query('OCA\Ldapusermanagement\GroupHooks')->register();

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