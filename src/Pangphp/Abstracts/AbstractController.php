<?php

namespace Pangphp\Abstracts;

abstract class AbstractController {

    protected $_app;
    protected $_auth;
    protected $_current_user;
    protected $_privileges;

    function __construct($app) {

        $this->_app = $app;
        $this->_auth = $this->_app->services->get('auth_service');
        $this->_privileges = $this->_app->services->get('privilege_service');
        $this->_current_user = $this->_auth->getAuthdUser();

    }

}