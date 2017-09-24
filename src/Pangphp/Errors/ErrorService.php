<?php

namespace Pangphp\Errors;

use \Doctrine\ORM\EntityManager;
use \Pangphp\Errors\Entities\Error;
use \Pangphp\Errors\ErrorMailService;
use \Noodlehaus\Config;

class ErrorService{
	
	protected $_em;
	protected $_config;
	protected $_env;

	protected $_e;
	protected $_code = 0;
	protected $_message;
	protected $_trace;

	protected $_instances;
	protected $_instance;

	public $error = [];
	
	public function __construct(EntityManager $em, Config $config){

		$this->_em = $em;
		$this->_config = $config;
		
	}

  function setEnvironment($env) {
		$this->_env = $env;
	}
	
	public function handleError(\Exception $e, $path){

		require $path .  "/../src/exceptions.php";
		require dirname(__FILE__) . "/../pang_exceptions.php";
		
		$this->_instances = array_merge($pangphp_exceptions, $app_exceptions);
		
		$this->setException($e);
		$this->setMessage($e->getMessage());
		$this->setCode($e->getCode());
		$this->setTrace($e->getTrace());
		$this->_instance = $this->getInstance();
		$this->error = [
				"status"  => false,
				"message" => $this->_message
		];

		if ($this->_env !== "development"){
			$this->inProduction();
			return;
		}
		$this->inDevelopment();
		
	}
	
	public function getInstance() {
		
		$instance_found = false;
		
		foreach($this->_instances as $instance) {
			if($this->_e == $instance['instance']) {
				$instance_found = $instance;
				break;
			}
		}

		return $instance_found;

	}
	
	public function insertException(){
		
		$insert_error = new Error();
		$insert_error->setInstance($this->_e);
		$insert_error->setCode($this->_code);
		$insert_error->setMessage($this->_message);
		$insert_error->setLoggedAt();
		
		$this->_em->persist($insert_error);
		$this->_em->flush();

	}
	
	public function inProduction (){
		
		if($this->_instance) {
			$this->error["code"] = $this->_instance["code"];
			if($this->_instance['message']) {
				$this->error["message"] = $this->_instance['message'];
			}

		}

		$this->insertException();
	}
	
	public function inDevelopment(){

		$this->error["exception"] = $this->_e;
		$this->error["trace"] = $this->_trace;

		if($this->_instance) {
			
			$this->error["code"] = $this->_instance["code"];
			$this->error["actual_message"] = $this->_message;

			if($this->_instance['message']) {
				$this->error["message"] = $this->_instance['message'];
			}

		} else {

			$this->error["message"] = "Error Instance Not Found: " . $this->_message;

		}

	}

	public function cleanErrorLogs() {
		
		if($this->_config->get('clean_error_log.delete_all')){
			$this->deleteAll();
		} else {
			if($this->_config->get('clean_error_log.limit') > 0){
				$this->deleteLimit();
			}
		}
	}
	
	public function deleteAll(){
		$qb = $this->_em->createQueryBuilder();
		
		$qb->delete("Pangphp\Errors\Entities\Error", "e")
			 ->where("e.id > :id")
			 ->setParameter("id", 0)
			 ->getQuery()
			 ->getResult();
	}
	
	public function deleteLimit(){

		$limit = $this->_config->get("clean_error_log.limit");
		
		if ($this->getTableRowCount() > $limit){
			$qb = $this->_em->createQueryBuilder();
			
			$start = $this->_em->getRepository("Pangphp\Errors\Entities\Error")
				->findOneBy(array(), array('id' => 'DESC'));
			
			(!is_null($start)) ? $start = $start->getId() : $start = 0;
			
			$end = $start - $limit;
			
			$qb->delete("Pangphp\Errors\Entities\Error", "e")
			->where("e.id <= :end")
			->setParameters(array(
				"end"   => $end,
			))
			->getQuery()
			->getResult();
			
		}
	}
	
	public function getTableRowCount(){
		$qb = $this->_em->createQueryBuilder();
		
		return $qb->select("COUNT(e.id)")
							->from("Pangphp\Errors\Entities\Error", "e")
							->getQuery()
							->getSingleScalarResult();
		
	}
	
	public function getCode(){
		return $this->_code;
	}
	
	public function setCode($code){
		$this->_code = $code;
	}
	
	public function getMessage(){
		return $this->_message;
	}
	
	public function setMessage($message){
		$this->_message = $message;
	}

	public function getTrace(){
		return $this->_trace;
	}
	
	public function setTrace($trace){
		$this->_trace = $trace;
	}
	
	public function getException(){
		return $this->_e;
	}
	
	public function setException(\Exception $e){
		$this->_e = get_class($e);
	}
}