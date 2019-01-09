<?php

$subAdmin = \OC::$server->getGroupManager()->getSubAdmin();
$ocConfig = \OC::$server->getConfig();

$application->add(new OCA\Ldapusermanagement\Command\GroupAdminsToLdap($subAdmin, $ocConfig));
