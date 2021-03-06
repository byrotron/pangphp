<?php 

namespace Pangphp;

use \Doctrine\ORM\Tools\Setup;
use \Doctrine\ORM\EntityManager;
use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;
use \Pangphp\Errors\ErrorMailService;
use \Pangphp\Sessions\SessionService;
use \Pangphp\Strings\StringService;
use \Pangphp\EditableLists\EditableListMysqlService;
use \Pangphp\Documents\DocumentService;
use \Pangphp\Documents\PrintService;
use \Pangphp\Documents\UploadService;
use \Pangphp\Search\SearchService;
use \Pangphp\Mail\MailService;
use \Elasticsearch\ClientBuilder;
use \Pangphp\Errors\ErrorService;
use \Noodlehaus\Config;

$services_array = [

	'logger' => function($c) {

		$logger = new Logger('dti_logger');
		$file_handler = new StreamHandler("../logs/app.log");
		$logger->pushHandler($file_handler);
		return $logger;

	},
	
	'session' => function($c) {
		return new SessionService($c["entity_manager"]);
	},
	
	'config' => function($c) {
	
		return new Config($c->get('settings')['config']);

	},

	'entity_manager' => function($c) {
	
		$isDevMode = $c->get('settings')['env'] === 'development' ? true : false;
		// if ($isDevMode) {
				$cache = new \Doctrine\Common\Cache\ArrayCache;
		// } else {
		// 		$cache = new \Doctrine\Common\Cache\ApcCache;
		// }

		$config = Setup::createAnnotationMetadataConfiguration($c->get('settings')['entities'], $isDevMode);
		$config->setMetadataCacheImpl($cache);
		$driverImpl = $config->newDefaultAnnotationDriver($c->get('settings')['entities']);
		$config->setMetadataDriverImpl($driverImpl);
		$config->setProxyDir( $c->get('settings')['public'] . "/../" . 'src/Proxies');
		$config->setProxyNamespace('App\Proxies');
	
		if ($isDevMode) {
				$config->setAutoGenerateProxyClasses(true);
		} else {
				$config->setAutoGenerateProxyClasses(false);
		}

		$db_config = $c->get("config");
		// database configuration parameters
		$connectionOptions  = array(
			'driver'   => $db_config->get('database.driver'),
			'user'     => $db_config->get('database.user'),
			'password' => $db_config->get('database.password'),
			'dbname'   => $db_config->get('database.db_name')
		);

		// obtaining the entity manager
		return EntityManager::create($connectionOptions, $config);
		
	},

	"string_service" => function($c) {
		return new StringService();
	},

	'editable_lists' => function($c) {
		return new EditableListMysqlService($c["entity_manager"]);
	},

	'document_service' => function($c) {
		return new DocumentService($c["string_service"]);
	},

	'print_service' => function($c) {
		return new PrintService($c["document_service"], $c['config']);
	},

	'upload_service' => function($c) {
		return new UploadService($c["document_service"]);
	},

	'elastic' => function($c) {
		return ClientBuilder::create()->build();
	},

	'search' => function($c) {
		return new SearchService($c["elastic"], $c['config']);
	},

	'mailer' => function($c) {
		return new \PHPMailer();
	},

	'mail' => function($c) {
		return new MailService($c["mailer"], $c["config"]);
	},
	
	"error_service" =>function($c) {
		$error_service = new ErrorService($c['entity_manager'], $c['config'], $c["mail"]);
		$error_service->setEnvironment($c->get('settings')["env"]);
		return $error_service;
	},
	
	"error_mail_service" =>function($c) {
		return new ErrorMailService($c['config'], $c['mail']);
	}
	
]

?>