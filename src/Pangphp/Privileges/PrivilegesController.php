<?php

namespace Pangphp\Privileges;

use \Pangphp\Abstracts\AbstractController;

class PrivilegesController extends AbstractController { 

  function __construct($app) {
    parent::__construct($app);
  }

  /**
   * @PrivateAction(
   *  label="View Privileges", 
   *  description="View all the different setting for each action and role")
   */
  function view_privileges() {
    
    $this->_auth->isAuthd();
    $result = $this->_privileges->protectedAction("privileges", "view_privileges", $this->_current_user);

    $service = $this->_app->services->get('privilege_service');

    $controllers = $service->getControllers()->getArrayResult();
    $roles = $service->getRoles()->getArrayResult();
    $privileges = $service->getPrivileges()->getArrayResult();

    $response_body = array(
        "status" => true,
        "message" => "Your request was successful",
        "result" => [
            "controllers" => $controllers,
            "roles" => $roles,
            "privileges" => $privileges
        ]
    );

    $newresponse = $this->_app->response->withJson($response_body);
    return $newresponse;

  }

  /**
   * @PrivateAction(
   *  label="Update Privilegse", 
   *  description="Change each action per user allowing you to determine what role can complete what action")
   */
  function update_privilege() {
    
    $this->_auth->isAuthd();
    $result = $this->_privileges->protectedAction("privileges", "update_privilege", $this->_current_user);

    $service = $this->_app->services->get('privilege_service');
    $body = $this->_app->request->getParsedBody();
    $privs = $service->updatePrivilege($body["action"], $body["role"], $body["status"]);

    $newresponse = $this->_app->response->withJson(array(
      "status" => true,
      "message" => "Your request was successful",
    ));
    return $newresponse;

  }
}