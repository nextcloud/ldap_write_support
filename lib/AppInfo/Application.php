<?php
namespace OCA\LdapUserManagement\AppInfo;

use \OCP\AppFramework\App;

use \OCA\LdapUserManagement\Service\UserService;


class Application extends App {

    public function __construct(array $urlParams=array()){
        parent::__construct('ldapusermanagement', $urlParams);

        $container = $this->getContainer();

        /**
         * Controllers
         */
        $container->registerService('UserService', function($c) {
            return new UserService(
                $c->query('UserManager')
            );
        });

        $container->registerService('UserManager', function($c) {
            return $c->query('ServerContainer')->getUserManager();
        });
    }
}
