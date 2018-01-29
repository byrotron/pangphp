<?php 

namespace Pangphp\Auth;

use \App\Users\Entities\User;

class AuthController {
  
  protected $_app;

  function __construct($app) {
    $this->_app = $app;
  }
  
  function login() {

    $data = $this->_app->request->getParsedBody();
    
    $user_service = $this->_app->services->get("user_service");
	  $auth = $this->_app->services->get("auth_service");
    
    $user_obj = $user_service->getUserByEmail($data["email"]);
    $user = $user_obj->getOneOrNullResult();
    
    if(!$user){
	    throw new AuthException("This account does not exist");
    }

    $auth->login($data["password"], $user);
    
    // Once everything is complete update the session id and respond
    // session_regenerate_id(true);

    $response_body = array(
        "status" => true,
        "message" => "Your request was successful",
        "result" => $user_obj->getArrayResult()[0]
    );

    $newresponse = $this->_app->response->withJson($response_body);
    return $newresponse;

  }

  function logout() {
	
	  $auth = $this->_app->services->get("auth_service");
	  $auth_token = isset($_SESSION["auth_token"]) ? $_SESSION["auth_token"] : false;
    
    if($auth_token) {
      $auth->logout($auth_token);
    }

    $response_body = array(
        "status" => true,
        "message" => "You have been logged out successfully"
    );
    
    $newresponse = $this->_app->response->withJson($response_body);
    return $newresponse;
  }

  function is_authd() {
      
    $auth = $this->_app->services->get("auth_service");
    $privs = $this->_app->services->get("privilege_service");

    if(isset($_SESSION["auth_token"])) {

      if($auth->isAuthd() === true) {

        $body = array(
          "status" => true,
          "result" => [
            "user" => $auth->getAuthdUser(),
            "privileges" => $privs->getRolePrivileges($auth->getAuthdUser('object')->getRole())
          ]
        );

        $newresponse = $this->_app->response->withJson($body);
        return $newresponse;

      }

    }

    throw new AuthException("You are not not logged in");

  } 

}