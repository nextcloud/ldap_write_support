<?php

use OCA\LdapWriteSupport\AppInfo\Application;

$app = new Application();
$app->registerLDAPPlugins();
$app->registerHooks();
