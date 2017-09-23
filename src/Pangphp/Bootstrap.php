<?php

namespace Pangphp;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Slim\Container;
use \Slim\App;

use \Slim\Exception\NotFoundException;
use \Pangphp\Exceptions\BootstrapException;

class Bootstrap {

    public $services;
    public $request;
    public $response;
    public $params;

    protected $_app;

    function __construct($ENV) {
      
      if( !in_array($ENV, ['production', 'development'] )) {
        throw new BootstrapException("Your environment must be set to on of either \"development\" or \"production\"");
      }

      $configuration = [
        'settings' => [
          'displayErrorDetails' => true,
          'ENV'=> $ENV
        ]
      ];
      
      $container = new Container($configuration);
      $this->_app = new App($container);

    }

    public function setServices($path) {
    
      $app_services_array = [];
      if(file_exists($path)) {
        $app_services_array = require $path;
      } else {
        throw new BootstrapException("Could not locate your services at " . $path); 
      }
      
      // Set built in services
      require dirname(__FILE__)  . '/services.php';

      $services = array_merge($services_array, $app_services_array);

      foreach($services as $k=>$v) {
          $this->services[$k] = $v;
      }

    }
    
    protected function _setSession() {
        
      $handler = $this->services->get("session");
      session_set_save_handler($handler, true);
      session_start();

    }
    
    protected function _setTimezone($time_zone) {

        ini_set("date.timezone", $time_zone);

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
        
      $arr = str_getcsv($this->request->getQueryParams()["url"], "/");

      $controller_str = $this->_setControllerString($arr[1]);

      if(class_exists($controller_str)) {
        $controller = new $controller_str($this);
        
        $action = $this->_setActionString($arr[2]);

        if(method_exists($controller, $action)) {

            return $controller->$action();

        } else {
            
            throw new \Exception("We could not find the requested action " . $this->request->getQueryParams()["url"]);
        }

      } else {
          throw new \Exception("We could not find the route for " . $this->request->getQueryParams()["url"]);
      }
    }


    protected function _setAPIRouting() {
      $this->_app->any('/api/[{path:.*}]', function (Request $request, Response $response) {
        
        try {

            $this->request = $request;
            $this->response = $response;
            return $this->_routing();
        
        } catch(\Exception $e) {
          
            $error = $this->get('error_service');
            $error->handleError($e);
    
            $newresponse = $response->withJson($error->error);
            return $newresponse;
    
        }
        
      });
    }
    
    function serveStaticFiles() {
        $var_path = $this->request->getAttribute("path");
        
        if(isset($var_path)) {
         
            $path = 'dist/' . $var_path;

            if(file_exists($path)) {

                $ext = pathinfo($path, PATHINFO_EXTENSION);
                $name = "Content-type";

                switch($ext) {
                    case 'css': $header = 'text/css';
                        break;
                    case 'js': $header = 'text/js';
                        break;
                    case 'map': $header = 'text/json';
                        break;
                    case 'woff2': $header = 'application/font-woff';
                        break;
                    case 'svg': $header = 'image/svg+xml';
                        break;
                    case 'png': $header = 'image/png';
                        break;
                    default: $header = 'text/plain';
                }

                $newresponse = $this->response->withHeader($name, $header)
                        ->write(file_get_contents($path));
                return $newresponse;
            }

        }
        return $this->response->getBody()->write(file_get_contents('dist/index.html'));
    }

    protected function _setStaticFileRouting() {
      $this->_app->get('/[{path:.*}]', function ($request, $response) {
        
        try {
          
          $this->request = $request;
          $this->response = $response;
          return $this->serveStaticFiles();
    
        } catch(\Exception $e) {
          
          // Over here we need to define specific pages for specific error codes
          // 500, 404
          $error = $this->get('error_service');
          $error->handleStaticError($e);
    
          $newresponse = $response->withJson($error->error);
          return $newresponse;
    
        }
      });
    }

    function run() {

      $this->_setTimezone();
      $this->_setSession();
      $this->_setAPIRouting();
      
      $this->_setStaticFileRouting();
      $this->_app->run();
    }
}
