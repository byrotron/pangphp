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

    $role_service = $this->_app->services->get("role_service");
    $data = $this->_app->request->getParsedBody();

    $role = $role_service->createRole($data["name"]);

    $newresponse = $this->_app->response->withJson( array(
      "status" => true,
      "message" => "Your request was successul",
      "result" => $role
    ));

    return $newresponse;
  }
   
    /**
   * @PrivateAction(
   *  label="View Roles", 
   *  description="View and manage the list of roles within the system")
   */
  function get_roles() {

    $this->_auth->isAuthd();
    $result = $this->_privileges->protectedAction("roles", "get_roles", $this->_current_user);

    $service = $service = $this->_app->services->get("role_service");
    $roles = $service->getRoles();
    
    $response_body = array(
      "status" => true,
      "result" => $roles
    );

    $newresponse = $this->_app->response->withJson($response_body);
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
    $data = $this->_app->request->getParsedBody();
    $updated = $service->updateRole($data["id"], $data["name"]);

    if($updated === true) {
      $response_body = array(
          "status" => true,
          "message" => "Your request was successful",
      );
      
    } else {
       $response_body = array(
          "status" => false,
          "message" => "This role does not exist",
          "result" => []
      );
    }

    $newresponse = $this->_app->response->withJson($response_body);
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
    $data = $this->_app->request->getParsedBody();
    $found = $service->deleteRole($data["id"]);

    if($found === true) {
      $response_body = array(
          "status" => true,
          "message" => "Your request was successful",
      );
      
    } else {
       $response_body = array(
          "status" => false,
          "message" => "This role does not exist"
      );
    }

    $newresponse = $this->_app->response->withJson($response_body);
    return $newresponse;

  }

}
