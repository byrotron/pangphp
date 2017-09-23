<?php

namespace Pangphp;

use \Slim\Container;
use \Slim\App;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Pangphp\Bootstrap;

define("PUBLIC_PATH", getcwd(), TRUE);
define("APP_PATH", getcwd() . DIRECTORY_SEPARATOR . '..', TRUE);
define("DATA_PATH", getcwd() . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data', TRUE);

require_once "../vendor/autoload.php";

// Here we can actually get the configuration details from config outside of the service
// It is not the cleansest however it will still work and work long term
$configuration = [
		'settings' => [
				'displayErrorDetails' => true,
				'mode'=>json_decode(file_get_contents(dirname(__FILE__)."/../../config/config.json"),true)
		]
];

$container = new Container($configuration);
$app = new App($container);

// This allows for the async requests for API and CRUD operations
$app->any('/api/[{path:.*}]', function (Request $request, Response $response) {
		
		try {
		
				$bootstrap = new Bootstrap($this, $request, $response);
				return $bootstrap->routing();
		
		} catch(\Exception $e) {
			
				$error = $this->get('error_service');
				$error->handleError($e);

				$newresponse = $response->withJson($error->error);
				return $newresponse;

		}
		
});

// The serving of static files and general navigation
$app->get('/[{path:.*}]', function ($request, $response) {

		try {
				
				$bootstrap = new Bootstrap($this, $request, $response);
				return $bootstrap->serve_static_files();

		} catch(\Exception $e) {
			
			// Over here we need to define specific pages for specific error codes
			// 500, 404
			$error = $this->get('error_service');
			$error->handleError($e);

			$newresponse = $response->withJson($error->error);
			return $newresponse;

		}
});


$app->run();