<?php

namespace Pangphp;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Slim\Container;
use \Slim\App;

use \Slim\Exception\NotFoundException;

class bootstrap {

    public $services;
    public $request;
    public $response;
    public $params;
    
    public static $instance;
    
    function __construct($services, Request $request = null, Response $response = null) {
        
        $this->services = $services;
        $this->request = $request;
        $this->response = $response;
        
        $this->set_services();

        $this->set_session();

        //Clear tmp folder
        $docs = $this->services->get("document_service");
        $docs->deleteExpiredTempFiles();
    }
    
    public function set_session() {
        
        $handler = $this->services->get("session");
        $config = $this->services->get('config');
        $this->setTimezone($config->get('time_zone'));

        session_set_save_handler($handler, true);
        
        //Start the session and regenerate the id
        session_start();

    }

    function set_services() {

        require APP_PATH . DIRECTORY_SEPARATOR . '/src/app/services.php';
        require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'services.php';

        $services = array_merge($services_array, $app_services_array);

        foreach($services as $k=>$v) {
            $this->services[$k] = $v;
        }
    }
    
    function setTimezone($time_zone) {
        ini_set("date.timezone", $time_zone); //'America/New_York'
    }
    
    function setControllerString($controller) {
        
        $name = str_replace(" ", "", ucwords(str_replace("-", " ", $controller)));
        $str = "\\App\\";
        $str .= $name;
        $str .= "\\" . $name . "Controller";
        return $str;
        
    }
    
    function setActionString($action) {

        return str_replace("-", "_", $action);
        
    }
    
    function routing() {
        
        $arr = str_getcsv($this->request->getQueryParams()["url"], "/");

        $controller_str = $this->setControllerString($arr[1]);
        
        
        if(class_exists($controller_str)) {
            $controller = new $controller_str($this);
            
            $action = $this->setActionString($arr[2]);

            if(method_exists($controller, $action)) {

                return $controller->$action();

            } else {
                
                throw new \Exception("We could not find the requested action " . $this->request->getQueryParams()["url"]);
            }

        } else {
            
            throw new \Exception("We could not find the route for " . $this->request->getQueryParams()["url"]);
        }
    }
    
    function serve_static_files() {
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
}
