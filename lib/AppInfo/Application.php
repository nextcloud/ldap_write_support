<?php
namespace OCA\Ldapusermanagement\AppInfo;

use \OCP\AppFramework\App;

// use \OCA\LdapUserManagement\Service\UserService;

use \OCA\Ldapusermanagement\UserHooks;
use \OCA\Ldapusermanagement\GroupHooks;


class Application extends App {

    public function __construct(array $urlParams=array()){
        parent::__construct('ldapusermanagement', $urlParams);

        $container = $this->getContainer();

        $container->registerService('UserHooks', function($c) {
            return new UserHooks(
                $c->query('ServerContainer')->getUserManager()
            );
        });

        $container->registerService('GroupHooks', function($c) {
            return new GroupHooks(
                $c->query('ServerContainer')->getGroupManager()
            );
        });


    }
}
