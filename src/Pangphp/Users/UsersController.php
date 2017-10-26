<?php 

namespace Pangphp\Users;

use Pangphp\Abstracts\AbstractController;

class UsersController extends AbstractController {

  function __construct($app) {
    parent::__construct($app);
  }

  /**
   * @PrivateAction(
   *  label="Create New User", 
   *  description="Allow the ability for users to create new users with credentials")
   */
  function create_user() {
  
    $this->_auth->isAuthd();
    $result = $this->_privileges->protectedAction("users", "create_user", $this->_current_user);
	
    $user_service = $this->_app->services->get("user_service");
    $data = $this->_app->request->getParsedBody();

    $user = $user_service->createUser($data["user"]);

    $response_body = array(
      "status" => true,
      "message" => "Your request was successful",
      "result" => $user
    );

    $newresponse = $this->_app->response->withJson($response_body);
    return $newresponse;

  }

  /**
   * @PrivateAction(
   *  label="User Table", 
   *  description="View a table of all the users in the system")
   */
  function view_users() {
      
    $this->_auth->isAuthd();
    $result = $this->_privileges->protectedAction("users", "view_users", $this->_current_user);

    $user_service = $this->_app->services->get("user_service");
    $params = $this->_app->request->getQueryParams();
    $filter = isset($params["filter"]) ? $params["filter"] : null;

    $users = $user_service->getUsers(
      $params["page"], 
      $params["limit"], 
      $params["orderby"], 
      $params["direction"],
      $filter
    );    

    $response_body = array(
        "status" => true,
        "message" => "Your request was successful",
        "result" => array(
            "users" => $users,
            "total_items" => $user_service->total_items
        )
    );

    $newresponse = $this->_app->response->withJson($response_body);
    return $newresponse;

  }

  /**
   * @PrivateAction(
   *  label="User Details", 
   *  description="View a specific users details and related information")
   */
  function view_user() {
    
    $this->_auth->isAuthd();
    $result = $this->_privileges->protectedAction("Users", "view_user", $this->_current_user);

    $params = $this->_app->request->getQueryParams();
    $service = $this->_app->services->get("user_service");
    
    $user = $service->getUser($params["id"])->getArrayResult();
    
    if(count($user)) {

      $response_body = array(
          "status" => true,
          "message" => "Your request was successful",
          "result" => $user[0]
      );
      
    } else {
       
       $response_body = array(
          "status" => false,
          "message" => "We could not find this user"
       );

    }

    $newresponse = $this->_app->response->withJson($response_body);
    return $newresponse;

  }

  /**
   * @PrivateAction(
   *  label="Edit User Details", 
   *  description="This will allow the editing of a users details")
   */
  function update_user() {

    $this->_auth->isAuthd();
    $result = $this->_privileges->protectedAction("users", "update_user", $this->_current_user);

    $data = $this->_app->request->getParsedBody();
    $user_service = $this->_app->services->get("user_service");
	  $user_data = $this->_app->request->getParams();

    $user = $user_service->updateUser($data["id"], $data["user"]);

    $response_body = array(
	    "status"  => true,
	    "message" => "Your request was successful",
	    "result"  => $user
    );

    $newresponse = $this->_app->response->withJson($response_body);
    return $newresponse;

  }

  /**
   * @PrivateAction(
   *  label="Delete User", 
   *  description="This will remove a user")
   */
  function delete_user() {

    $this->_auth->isAuthd();
    $result = $this->_privileges->protectedAction("users", "delete_user", $this->_current_user);

    $params = $this->_app->request->getQueryParams();
    $service = $this->_app->services->get("user_service");

    $service->deleteUser($params["id"]);
    $response_body = array(
        "status" => true,
        "message" => "Your request was successful"
    );

    $newresponse = $this->_app->response->withJson($response_body);
    return $newresponse;

  } 

}