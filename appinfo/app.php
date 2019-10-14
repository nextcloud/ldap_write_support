<?php

use OCA\LdapWriteSupport\AppInfo\Application;

$app = \OC::$server->query(Application::class);
$app->registerLDAPPlugins();
