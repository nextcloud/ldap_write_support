<?php
namespace OCA\LdapUserManagement\AppInfo;

use \OCP\AppFramework\App;

// use \OCA\LdapUserManagement\Service\UserService;

use \OCA\LdapUserManagement\UserHooks;

class Application extends App {

    public function __construct(array $urlParams=array()){
        parent::__construct('ldapusermanagement', $urlParams);

        $container = $this->getContainer();

        $container->registerService('UserHooks', function($c) {
            return new UserHooks(
                $c->query('ServerContainer')->getUserManager()
            );
        });

    }
}
