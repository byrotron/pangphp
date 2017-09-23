<?php 

namespace Pangphp\Auth;


class AuthControllerTest extends PHPUnit_Framework_TestCase {
  
  protected $_app;

  function __construct($app) {
    $this->_app = $app;
  }
  
  function login() {

    $var = new Buonzz\Template\YourClass;
    $this->assertTrue(is_object($var));
    unset($var);

  }

  function logout() {
	
	  
  }

  function is_authd() {

  }

}