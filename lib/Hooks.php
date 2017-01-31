<?php

namespace OCA\Ldapusermanagement;

class UserHooks {

    private $userManager;

    public function __construct($userManager){
        $this->userManager = $userManager;
    }

    static public function register() {
        $callback = function($user) {

            $fid = fopen('/var/www/html/server/apps/ldapusermanagement/log.txt', 'w');
            fwrite($fid, "new Hook mode \n");
            fclose($fid);
        };
        $this->userManager->listen('\OC\User', 'preDelete', $callback);
    }

}