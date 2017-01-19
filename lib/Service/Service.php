<?php
namespace OCA\LdapUserManagement\Service;

class UserService {

    private $userManager;

    public function __construct($userManager){
        $this->userManager = $userManager;
    }

    public function delete($userId) {
        return $this->userManager->get($userId)->delete();
    }

    // recoveryPassword is used for the encryption app to recover the keys
    public function setPassword($userId, $password, $recoveryPassword) {
        return $this->userManager->get($userId)->setPassword($password, $recoveryPassword);
    }

    public function disable($userId) {
        return $this->userManager->get($userId)->setEnabled(false);
    }

    public function getHome($userId) {
        return $this->userManager->get($userId)->getHome();
    }
}
