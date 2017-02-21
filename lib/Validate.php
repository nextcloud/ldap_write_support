<?php

namespace OCA\Ldapusermanagement;

use OCP\AppFramework\App;

class Validate {
	public function __construct()
	{
		if (\OCP\App::isEnabled('user_ldap')) {
	        $message = "<<<<<<<<<enabled>>>>>>>>>>";
		    \OC::$server->getLogger()->notice($message, array('app' => 'ldapusermanagement'));
		}
	}
}


