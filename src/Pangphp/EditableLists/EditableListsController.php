<?php

namespace Pangphp\EditableLists;
use \Pangphp\Abstracts\AbstractController;

abstract class EditableListsController extends AbstractController {

  function __construct($app) {
    parent::__construct($app);
  }

  function add_list_item() {

    // $this->_auth->isAuthd();
    // $result = $this->_privileges->protectedAction("EditableList", "add_list_item", $this->_current_user);

    $list = $this->_app->services->get("editable_lists");
    $body = $this->_app->request->getParsedBody();
    $id = isset($body["id"]) ? $body["id"] : null;
    $val = $list->addListItem($id, $body["name"], $body["group"], $body["item"]);

    $response_body = array(
      "status" => true,
      "message" => "Your request was successful",
      "result" => $val->getId()
    );

    $newresponse = $this->_app->response->withJson($response_body);
    return $newresponse;

  }

  function remove_list_item() {
    $this->_auth->isAuthd();

    $list = $this->_app->services->get("editable_lists");
    $body = $this->_app->request->getParsedBody();

    $list->removeListItem($body["id"]);

    $response_body = array(
      "status" => true,
      "message" => "Your request was successful"
    );

    $newresponse = $this->_app->response->withJson($response_body);
    return $newresponse;
  }

  function edit_list_item() {
    $this->_auth->isAuthd();

    $list = $this->_app->services->get("editable_lists");
    $body = $this->_app->request->getParsedBody();

    $list->editListItem($body["id"], $body["data"]);

    $response_body = array(
      "status" => true,
      "message" => "Your request was successful"
    );

    $newresponse = $this->_app->response->withJson($response_body);
    return $newresponse;
  }

  function get_list() {
    $list = $this->_app->services->get("editable_lists");
    $params = $this->_app->request->getQueryParams();

    $items = $list->getListitems($params["group"], $params["list"]);
    $array = count($items->getArrayResult()) > 0 ? $items->getArrayResult()[0] : new \stdClass();

    $response_body = array(
      "status" => true,
      "message" => "Your request was successful",
      "result" => $array
    );

    $newresponse = $this->_app->response->withJson($response_body);
    return $newresponse;
  }
  
}