<?php

use \Doctrine\ORM\Tools\Setup;
use \Doctrine\ORM\EntityManager;
use \App\Privileges\Entities\Controller;
use \App\Privileges\Entities\Action;
use \App\Privileges\Entities\Privilege;
use \App\Roles\Role;
use \App\Privileges\PrivilegeMysqlService;
use \Doctrine\Common\Annotations\AnnotationReader;

require_once "vendor/autoload.php";

class InstallPrivileges {

  protected $_path;
  protected $_dir;
  protected $_actions = [];
  protected $_controller;
  protected $_action;
  protected $_reader;

  function __construct($app, $em) {
    
    $this->_em = $em;
    $this->_path = dirname(__FILE__) . '/src/app/';
    $this->_dir = scandir($this->_path);
    $this->_reader = new AnnotationReader();

  }
  
  function install() {
    try {
      
      $this->removeControllers();
      $this->removeUnusedActions();
      $this->setActionsArray();
      $this->updateActions();
      $privilege = new PrivilegeMysqlService($em);
      $privilege->generate();

    } catch(Exception $e) {

      echo $e->getMessage() . PHP_EOL;

    }
  }

  function update() {

  }

  /**
   * Set the private controllers and actions from the annotations
   */
  function setActionsArray() {
    foreach($this->_dir as $dir_name) {
      $controller = $this->_path . $dir_name . DIRECTORY_SEPARATOR . $dir_name . "Controller";

      if(file_exists($controller . ".php")) {
        
        $this->setControllerArray($dir_name);

      }

    }

  }

  protected function setControllerArray($controller_name) {
    $controller = "App\\". $controller_name ."\\". $controller_name ."Controller";
    $ref_controller = new ReflectionClass($controller);
    $class_annotation = $this->_reader->getClassAnnotation($ref_controller, 'PrivateController');

    if($class_annotation instanceof PrivateController) {

      $this->_actions[$controller_name] = [
        "label" => $class_annotation->label,
        "description" => $class_annotation->description,
        "actions" => []
      ];

      foreach($ref_controller->getMethods() as $method) {

        $this->setMethodArray($controller_name, $method);

      } 
    
    }
  }

  protected function setMethodArray($controller_name, $method) {
    $controller = "App\\". $controller_name ."\\". $controller_name ."Controller";
    $ref_method = new ReflectionMethod($controller, $method->name);
    $method_annotations = $this->_reader->getMethodAnnotation($method, 'PrivateAction');

    if($method_annotations) {

      array_push($this->_actions[$controller_name]["actions"], [
        "action" => $method->name,
        "label" => $method_annotations->label,
        "description" => $method_annotations->description
      ]);

    }
  }

  protected function getActionMethods($class) {
    $arr = [];
    foreach($class->getMethods() as $method) {
      if($this->isAction($method->name)) {
        $arr[] = $method->name;
      }
    }
    return $arr;
  }

  protected function countActionMethods($class) {
    $i = 0;
    foreach($class->getMethods() as $method) {
      if($this->isAction($method->name) === true) {
        $i++;
      }
    }
    return $i;
  }

  protected function isAction($method_name) {
    $arr = explode("_", $method_name);
    $is_action = array_pop($arr);
    return $is_action === 'action';
  }

  protected function createController($controller_name) {
    $result = $this->_em->getRepository('App\Privileges\Entities\Controller')
      ->findOneBy([
        'controller' => $controller_name
      ]);

    if($result) {
      $controller = $result;
    } else {
      $controller = new Controller();
    }

    return $controller;
  }

  protected function createAction($action_name) {
    $action_result = $this->_em->getRepository('App\Privileges\Entities\Action')
      ->findOneBy([
        'action' => $action_name
      ]);
      
    // If the action name has not yet been created then create it
    if(!$action_result) {
      return new Action();
    }

    return false;
  }

  function updateActions() {

    foreach($this->_actions as $controller_name => $controller) {
      
      $new_controller = $this->createController($controller_name);
      $new_controller->setController($controller_name);
      $new_controller->setLabel($controller["label"]);
      $new_controller->setDescription($controller["description"]);

      foreach($controller["actions"] as $action) {

        $new_action = $this->createAction($action["action"]);

        if($new_action) {
          $new_action->setAction($action["action"]);
          $new_action->setLabel($action["label"]);
          $new_action->setDescription($action["description"]);
          $new_action->setController($new_controller);
          $this->_em->persist($new_action);
          $new_controller->addActions($new_action);

        }

      }

      $this->_em->persist($new_controller);
    }
    $this->_em->flush();
    
  }
  
  protected function removeUnusedActions() {
    $actions = $this->_em->getRepository('App\Privileges\Entities\Action')
      ->findAll();

    foreach($actions as $action) {

      $controller = $action->getController();
      $controller_name = "App\\". $controller->getController() ."\\". $controller->getController() ."Controller";

      if(method_exists($controller_name, $action->getAction())) {

        $ref_method = new ReflectionMethod($controller_name, $action->getAction());
        $method_annotation = $this->_reader->getMethodAnnotation($ref_method, 'PrivateAction');
        if(!$method_annotation instanceof PrivateAction) {
          $this->_em->remove($action);
        }

      } else {
        
        $this->_em->remove($action);

      }
      

    }
    $this->_em->flush();

  }

  protected function removeControllers() {
    $controllers = $this->_em->getRepository('App\Privileges\Entities\Controller')
      ->findAll();

    foreach($controllers as $controller) {

      $controller_name = "App\\". $controller->getController() ."\\". $controller->getController() ."Controller";

      if(class_exists($controller_name)) {

        $ref_class = new ReflectionClass($controller_name);
        $class_annotation = $this->_reader->getClassAnnotation($ref_class, 'PrivateController');
        if(!$class_annotation instanceof PrivateController) {
          $this->_em->remove($controller);
        }

      } else {
        
        $this->_em->remove($controller);

      }
      
    }
    $this->_em->flush();

  }

}


?>