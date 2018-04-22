<?php

namespace Pangphp\Abstracts;

use Pangphp\Bootstrap;
use Pangphp\Auth\AuthService;
use App\Users\Entities\User as AppUser;
use Pangphp\Privileges\PrivilegeMysqlService;

abstract class AbstractController {

    /**
     * @var Bootstrap $_app
     */
    protected $_app;

    /**
     * @var AuthService $_auth
     */
    protected $_auth;

    /**
     * @var AppUser $_current_user
     */
    protected $_current_user;

    /**
     * @var PrivilegeMysqlService $_privileges
     */
    protected $_privileges;

    function __construct($app) {

        $this->_app = $app;
        $this->_auth = $this->_app->services->get('auth_service');
        $this->_privileges = $this->_app->services->get('privilege_service');
        $this->_current_user = $this->_auth->getAuthdUser('object');

    }

}