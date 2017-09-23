<?php

namespace Pangphp\Users\Entities;


abstract class UserStatus {
	
	/**
	 * @Id
	 * @GeneratedValue(strategy="AUTO")
	 * @Column(type="integer")
	 */
	protected $id;
	
	/**
	 * @Column(type="string", length=50, nullable=false )
	 */
	protected $name;
	
	/**
	 * @Column(type="text", nullable=true, options={"default" : NULL})
	 */
	protected $description;
	
	
	public function getId(){
		return $this->id;
	}

	public function setId($id){
		$this->id = $id;
	}
	
	public function getName(){
		return $this->name;
	}
	
	public function setName($name){
		$this->name = $name;
	}
	
	public function getDescription(){
		return $this->description;
	}
	
	public function setDescription($description){
		$this->description = $description;
	}
	
	public function getUsers(){
		return $this->users;
	}
	
	public function setUsers($users){
		$this->users = $users;
	}
	
}