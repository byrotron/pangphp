<?php

namespace Pangphp\Errors;

use \Doctrine\ORM\EntityManager;
use \Pangphp\Errors\Entities\SystemError;
use \Pangphp\Errors\Entities\ClientError;
use \Pangphp\Errors\ErrorMailService;
use \Noodlehaus\Config;
use \Pangphp\Mail\MailService;

class ErrorService {
	
	/**
	 * @var EntityManager
	 */
	protected $_em;

	/**
	 * @var Config
	 */
	protected $_config;

	/**
	 * @var MailService
	 */
	protected $_mailer;

	protected $_env;
	public $error;
	
	public function __construct(EntityManager $em, Config $config, MailService $mail){

		$this->_em = $em;
		$this->_config = $config;
		$this->_mailer = $mail;
		
	}

  function setEnvironment($env) {
		$this->_env = $env;
	}
	
	public function handleError($e, $path){

		$this->error = new \stdClass();
		$this->error->status = false;
		if ($this->_env === "development"){
			$this->error->trace = $e->getTrace();
			$this->error->actual_message = $e->getMessage();
		}

		if(new \Pangphp\ClientException() instanceof $e) {
			$this->error->message = $e->getMessage();
			$this->insertClientException($e);
		} else {
			$this->error->message = "Your request failed, if this continues, please contact your system adminsitrator";
			$this->insertSystemException($e);
		}

		return $this->error;
	}
	
	public function insertSystemException($e){
		
		$error = new SystemError();
		$error->setLine($e->getLine());
		$error->setFile($e->getFile());
		$error->setTrace(json_encode($e->getTrace()));
		$error->setMessage($e->getMessage());
		$error->setLoggedAt();
		
		// $this->_em->resetEntityManager();
		$this->_em->persist($error);
		$this->_em->flush();

	}
	
	public function insertClientException(\Exception $e){
		
		$error = new ClientError();
		$error->setLine($e->getLine());
		$error->setFile($e->getFile());
		$error->setTrace(json_encode($e->getTrace()));
		$error->setMessage($e->getMessage());
		$error->setLoggedAt();
		
		// $this->_em->resetEntityManager();
		$this->_em->persist($error);
		$this->_em->flush();

	}	
	
	public function cleanSystemErrors($days = 7){

		$limit = $this->_config->get("error_log.limit");		

		$qb = $this->_em->createQueryBuilder();
		$date = new \DateTime();
		$back_date = $date->sub(new \DateInterval('P7D'));
		
		$qb->delete("Pangphp\Errors\Entities\SystemError", "e")
		->where("e.logged_at <= :end")
		->setParameters(array(
			"end" => $back_date->format('Y-m-d h:i'),
		))
		->getQuery()
		->getResult();

	}
	
	public function cleanClientErrors($days = 7){

		$limit = $this->_config->get("error_log.limit");		

		$qb = $this->_em->createQueryBuilder();
		$date = new \DateTime();
		$back_date = $date->sub(new \DateInterval('P7D'));
		
		$qb->delete("Pangphp\Errors\Entities\ClientError", "e")
		->where("e.logged_at <= :end")
		->setParameters(array(
			"end" => $back_date->format('Y-m-d h:i'),
		))
		->getQuery()
		->getResult();

	}

	public function sendErrorTable() {

		$errors = $this->_em->getRepository('Pangphp\Errors\Entities\SystemError')->findAll();
		$this->_mailer->sendToAdmin();
		$this->_mailer->sendFromAdmin();
		$this->_mailer->setTemplate(dirname(__FILE__) . DIRECTORY_SEPARATOR ."Emails". DIRECTORY_SEPARATOR . "error.email.php");
		
		$this->_mailer->setData([
			"name" => "Administration Team",
			"url" => $this->_config->get("url"),
			"errors" => $errors,
		]);
		
		$this->_mailer->sendmail();
	}
	
}