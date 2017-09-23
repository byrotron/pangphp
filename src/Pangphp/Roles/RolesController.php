<?php 

namespace Pangphp\Roles;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use \Pangphp\Abstracts\AbstractController;

class RolesController extends AbstractController {
  
  function __construct($app) {
    parent::__construct($app);
  }

  /**
   * @PrivateAction(
   *  label="Create New Role", 
   *  description="Create a new role to provide your own specific privileges for users")
   */
  function create_role() {

    $this->_auth->isAuthd();
    $result = $this->_privileges->protectedAction("roles", "create_role", $this->_current_user);

    $role = $this->_app->services->get("role_service");
    $body = $this->_app->request->getParsedBody();

    $new_role = $role->create_role($body["name"]);

    $newresponse = $this->_app->response->withJson( array(
        "status" => true,
        "message" => "Your request was successul",
        "result" => $new_role->get_array()
    ));

    return $newresponse;
  }
   
    /**
   * @PrivateAction(
   *  label="View Roles", 
   *  description="View and manage the list of roles within the system")
   */
  function view_roles() {

    $this->_auth->isAuthd();
    $result = $this->_privileges->protectedAction("roles", "view_roles", $this->_current_user);

    $service = $service = $this->_app->services->get("role_service");
    $roles = $service->get_roles();
    
    if(count($roles) > 0) {
      $body = array(
          "status" => true,
          "message" => "Your request was successful",
          "result" => array(
             "roles" => $roles
          )
      );
      
    } else {
       $body = array(
          "status" => false,
          "message" => "We could not find any roles",
          "result" => []
      );
    }

    $newresponse = $this->_app->response->withJson($body);
    return $newresponse;

  }

   /**
   * @PrivateAction(
   *  label="Update Role Details", 
   *  description="Change the role name")
   */
  function update_role() {

    $this->_auth->isAuthd();
    $result = $this->_privileges->protectedAction("roles", "update_role", $this->_current_user);

    $service = $service = $this->_app->services->get("role_service");
    $body = $this->_app->request->getParsedBody();
    $updated = $service->update_role($body["id"], $body["name"]);

    if($updated === true) {
      $body = array(
          "status" => true,
          "message" => "Your request was successful",
      );
      
    } else {
       $body = array(
          "status" => false,
          "message" => "This role does not exist",
          "result" => []
      );
    }

    $newresponse = $this->_app->response->withJson($body);
    return $newresponse;

  }

  /**
   * @PrivateAction(
   *  label="Delete Roles", 
   *  description="This will allow you delete none default based roles")
   */
  function delete_role() {
    
    $this->_auth->isAuthd();
    $result = $this->_privileges->protectedAction("roles", "delete_role", $this->_current_user);

    $service = $service = $this->_app->services->get("role_service");
    $body = $this->_app->request->getParsedBody();
    $found = $service->delete_role($body["id"]);

    if($found === true) {
      $body = array(
          "status" => true,
          "message" => "Your request was successful",
      );
      
    } else {
       $body = array(
          "status" => false,
          "message" => "This role does not exist"
      );
    }

    $newresponse = $this->_app->response->withJson($body);
    return $newresponse;

  }

}
