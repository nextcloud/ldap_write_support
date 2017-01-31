<?php
/**
 * Create your routes in here. The name is the lowercase name of the controller
 * without the controller part, the stuff after the hash is the method.
 * e.g. page#index -> OCA\LdapUserManagement\Controller\PageController->index()
 *
 * The controller class has to be registered in the application.php file since
 * it's instantiated in there
 */
return [
    'routes' => [
	   ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
	   ['name' => 'page#do_echo', 'url' => '/echo', 'verb' => 'POST'],

	[
		'name' => 'ldapusermanagement#updateStylesheet',
		'url' => '/ajax/updateStylesheet',
		'verb' => 'POST'
	],
	[
		'name' => 'ldapusermanagement#undo',
		'url' => '/ajax/undoChanges',
		'verb' => 'POST'
	],
	[
		'name' => 'ldapusermanagement#updateLogo',
		'url' => '/ajax/updateLogo',
		'verb' => 'POST'
	],
	[
		'name' => 'ldapusermanagement#getStylesheet',
		'url' => '/styles',
		'verb' => 'GET',
	],
	[
		'name' => 'ldapusermanagement#getLogo',
		'url' => '/logo',
		'verb' => 'GET',
	],
	[
		'name' => 'ldapusermanagement#getLoginBackground',
		'url' => '/loginbackground',
		'verb' => 'GET',
	],
	[
		'name' => 'ldapusermanagement#getJavascript',
		'url' => '/js/Ldapusermanagement',
		'verb' => 'GET',
	],
	[
		'name'	=> 'Icon#getFavicon',
		'url' => '/favicon/{app}',
		'verb' => 'GET',
		'defaults' => array('app' => 'core'),
	],
	[
		'name'	=> 'Icon#getTouchIcon',
		'url' => '/icon/{app}',
		'verb' => 'GET',
		'defaults' => array('app' => 'core'),
	],
	[
		'name'	=> 'Icon#getThemedIcon',
		'url' => '/img/{app}/{image}',
		'verb' => 'GET',
		'requirements' => array('image' => '.+')
	],
]];

