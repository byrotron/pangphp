<?php

namespace Pangphp;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Slim\Container;
use \Slim\App;
use \Slim\Exception\NotFoundException;
use \Pangphp\Exceptions\BootstrapException;

class Bootstrap {

    public $request;
    public $response;
    public $params;
    public $services;
    public $entities;

    protected $_app_path;
    protected $_container;

    function __construct($path, $env) {

      if(!in_array($env, ["development", "production"])) {
        throw new BootstrapException("Your environment variable must be set to either \"development\" or \"production\"");
      }

      $config_path = $path . "/../config/" . $env . "-config.json";
      if(!file_exists($config_path)) {
        throw new BootstrapException("We could not find your config file at " . $config_path);
      }

      $entities_path = $path . "/../src/entities.json";
      if(!file_exists($config_path)) {
        throw new BootstrapException("We could not find your entities file at " . $entities_path);
      }

      $services_path = $path . "/../src/services.php";
      if(!file_exists($services_path)) {
        throw new BootstrapException("We could not find your services file at " . $services_path);
      }

      $this->_app_path = $path;

      $container = new Container([
        "settings" => [
          "displayErrorDetails" => true,
          "env" => $env,
          "config" => $config_path,
          "entities" => $this->_setEntities($entities_path)
        ]
      ]);

      $this->_app = new App($container);
      $this->services = $this->_app->getContainer();
      $this->setServices($services_path);

    }

    public function setServices($path) {
    
      $app_services_array = [];
      if(file_exists($path)) {
        $app_services_array = require $path;
      } else {
        throw new BootstrapException("Could not locate your services at " . $path); 
      }
      
      // Set built in services
      require dirname(__FILE__)  . "/services.php";

      $services = array_merge($services_array, $app_services_array);

      foreach($services as $k=>$v) {
          $this->services[$k] = $v;
      }

    }


  protected function _setPangEntities() {
    $prepared_entities = [];
    $entities = json_decode(file_get_contents(dirname(__FILE__) . "/entities.json"), true);
    
    foreach($entities as $k => $folder) {
      
      $prepared_entities[$k] = dirname(__FILE__) . "/" . $folder . "/" . "Entities";
    
      if(!is_dir($prepared_entities[$k])) {
        throw new BootstrapException("Could not find: " . $prepared_entities[$k]);
      }
    
    }
    return $prepared_entities;
  }

  protected function _setAppEntities($path) {
    $prepared_entities = [];
    $entities = json_decode(file_get_contents($path), true);

    foreach($entities as $k => $folder) {
      
      $prepared_entities[$k] = $this->_app_path . "/../src/" . $folder . "/" . "Entities";
    
      if(!is_dir($prepared_entities[$k])) {
        throw new BootstrapException("Could not find: " . $prepared_entities[$k]);
      }
    
    }
    return $prepared_entities;
  }

  protected function _setEntities($path) {
    
    return array_merge($this->_setPangEntities(), $this->_setAppEntities($path));

  }
  
    
    protected function _setSession() {
        
      $handler = $this->services->get("session");
      session_set_save_handler($handler, true);
      session_start();

    }
    
    protected function _setTimezone() {

      $config = $this->services->get("config");
      ini_set("date.timezone", $config->get("time_zone"));

    }
    
    protected function _setControllerString($controller) {
        
      $name = str_replace(" ", "", ucwords(str_replace("-", " ", $controller)));
      $str = "\\App\\";
      $str .= $name;
      $str .= "\\" . $name . "Controller";
      return $str;
        
    }
    
    protected function _setActionString($action) {

        return str_replace("-", "_", $action);
        
    }
    
    protected function _routing() {
        
      $arr = str_getcsv($this->request->getAttribute("path"), "/");
      $controller_str = $this->_setControllerString($arr[0]);

      if(class_exists($controller_str)) {
        $controller = new $controller_str($this);
        
        $action = $this->_setActionString($arr[1]);

        if(method_exists($controller, $action)) {

            return $controller->$action();

        } else {
            
            throw new \Exception("We could not find the requested action " . $this->request->getUri());
        }

      } else {
          throw new \Exception("We could not find the route for " . $this->request->getUri());
      }
    }


    function asyncRouting($request, $response) {
      
        try {

          $this->request = $request;
          $this->response = $response;
            return $this->_routing();
        
        } catch(\Exception $e) {
          
            $error = $this->services->get("error_service");
            $error->handleError($e, $this->_app_path);
    
            $newresponse = $response->withJson($error->error);
            return $newresponse;
    
        }

    }
    
    protected function _serveStaticFiles() {
        $var_path = $this->request->getAttribute("path");

        if(isset($var_path)) {
         
            $path = $this->_app_path . "/dist/" . $var_path;

            if(file_exists($path)) {

                $ext = pathinfo($path, PATHINFO_EXTENSION);
                $name = "Content-type";

                switch($ext) {
                    case "css": $header = "text/css";
                        break;
                    case "js": $header = "text/js";
                        break;
                    case "map": $header = "text/json";
                        break;
                    case "woff2": $header = "application/font-woff";
                        break;
                    case "svg": $header = "image/svg+xml";
                        break;
                    case "png": $header = "image/png";
                        break;
                    default: $header = "text/plain";
                }

                $newresponse = $this->response->withHeader($name, $header)
                        ->write(file_get_contents($path));
                return $newresponse;
            }

        }
        return $this->response->getBody()->write(file_get_contents("dist/index.html"));
    }

    function staticRouting($request, $response) {
      
      try {
        
        $this->request = $request;
        $this->response = $response;
        return $this->_serveStaticFiles();
  
      } catch(\Exception $e) {
        
        // Over here we need to define specific pages for specific error codes
        // 500, 404
        $error = $this->get("error_service");
        $error->handleStaticError($e);
  
        $newresponse = $response->withJson($error->error);
        return $newresponse;
  
      }

    }

    function run() {
      
      $this->_setTimezone();
      $this->_setSession();
      $this->_app->any("/api/[{path:.*}]", array($this, "asyncRouting"));
      $this->_app->get("/[{path:.*}]", array($this, "staticRouting"));
      $this->_app->run();

    }

}
