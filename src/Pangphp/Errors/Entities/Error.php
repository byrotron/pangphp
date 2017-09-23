<?php

namespace Pangphp\Errors\Entities;

/**
 * @Entity
 * @Table(name="errors")
 */
class Error {
	
	/**
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue()
	 */
	protected $id;
	
	/**
	 * @Column(type="string", nullable=false)
	 */
	protected $instance_name;
	
	/**
	 * @Column(type="integer", nullable=false, options={"default" : 0})
	 */
	protected $code;
	
	/**
	 * @Column(type="text", nullable=true)
	 */
	protected $message;
	
	/**
	 * @Column(type="date", nullable=true)
	 */
	protected $logged_at;
	
	public function getId(){
		return $this->id;
	}
	
	public function setId($id){
		$this->id = $id;
	}
	
	public function getInstance(){
		return $this->instance_name;
	}
	
	public function setInstance($instance){
		$this->instance_name = $instance;
	}
	
	public function getCode(){
		return $this->code;
	}
	
	public function setCode($code){
		$this->code = $code;
	}
	
	public function getMessage(){
		return $this->message;
	}
	
	public function setMessage($message){
		$this->message = $message;
	}
	
	public function getLoggedAt(){
		return $this->logged_at;
	}
	
	public function setLoggedAt() {
		$this->logged_at = new \DateTime('now');
	}
	
}