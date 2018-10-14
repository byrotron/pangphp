<?php

namespace Pangphp\Errors;

use \Pangphp\Mail\MailService;
use \Noodlehaus\Config;
use \Pangphp\Errors\Entities\Error;

class ErrorMailService {
	
	protected $_config;
	protected $_mailer;
	
	public function __construct(Config $_config, MailService $_mailer){
		$this->_config = $_config;
		$this->_mailer = $_mailer;
	}
	
	public function sendErrorReport() {
		
		$summary_data = $this->errorReportSummaryData();
		
		if($this->_config->get("mail_exception_summary.short_list_amount") > 0)
			$short_list = $this->errorShortListData($this->_config->get("mail_exception_summary.short_list_amount"));
		
		$this->_mailer->sendToAdmin();
		$this->_mailer->sendFromAdmin();
		
		$this->_mailer->setTemplate(dirname(__FILE__) . DIRECTORY_SEPARATOR ."Emails". DIRECTORY_SEPARATOR . "error.email.php");
		
		$this->_mailer->setData([
			"name"          => "Administration Team",
			"url"           => $_SERVER['HTTP_HOST'],
			"summary_data"  => $summary_data,
			"short_list"    => $short_list,
		]);
		
		$this->_mailer->sendmail();
		
	}
	
	public function errorReportSummaryData() {
		
		$interval = $this->_config->get("mail_exception_summary.interval");
		$period = $this->_config->get("mail_exception_summary.period");
		
		$end_date = new \DateTime();
		$start_date = new \DateTime();
		
		$start_date->modify('-'.$interval.' '.$period);
		
		return $this->getData($start_date, $end_date);;
	}
	
	public function errorShortListData($amount) {
		$qb = $this->_em->createQueryBuilder();
		
		return $qb->select("e")
			->from("Pangphp\Errors\Entities\Error", "e")
			->setMaxResults($amount)
			->orderBy("e.id", "DESC")
			->getQuery()
			->getArrayResult();
	}
	
	public function getData($start_date, $end_date) {
		
		$distinct_instances = $this->getDistinctInstances();
		
		if(is_array($distinct_instances)){
			foreach($distinct_instances as $key => $instance){
				$qb = $this->_em->createQueryBuilder();
				
				$check = $qb->select("COUNT(e.instance_name) as result")
										->from("Pangphp\Errors\Entities\Error", "e")
										->where("e.instance_name = :instance")
										->andWhere("e.logged_at BETWEEN :startDate AND :endDate")
										->setParameters(array(
												"startDate" => $start_date->format('Y-m-d'),
												"endDate"   => $end_date->format('Y-m-d'),
												'instance'  => $instance['instance_name']
											)
										)
										->getQuery()
										->getArrayResult();
				
				if($check[0]['result'] > 0)
					$distinct_instances[$key]['result'] = $check[0]['result'];
			}
		}
		
		return $distinct_instances;
	}
	
	public function getDistinctInstances(){
		$qb = $this->_em->createQueryBuilder();
		
		return $qb->select(array("DISTINCT e.instance_name","e.code"))
							->from("Pangphp\Errors\Entities\Error", "e")
							->getQuery()
							->getArrayResult();
	}
	
}