<?php 

namespace Pangphp\Users\Entities;

use \App\Roles\Entities\Role as AppRole;
use \App\Users\Entities\UserStatus as AppUserStatus;

abstract class User {

  /** 
    * @Id
    * @Column(type="integer") 
    * @GeneratedValue()
    */
    protected $id;

    /** 
    * @Column(type="string", nullable=false) 
    */
    protected $name;

     /** 
    * @Column(type="string", nullable=false) 
    */
    protected $surname;

     /** 
    * @Column(type="string", nullable=false, unique=true) 
    */
    protected $email;

     /** 
    * @Column(type="string", nullable=false) 
    */
    protected $password;
	
	/**
	 * @ManyToOne(targetEntity="App\Users\Entities\UserStatus")
	 */
    protected $status;

   /**
    * @ManyToOne(targetEntity="App\Roles\Entities\Role")
    */
    protected $role;

    /** 
    * @Column(type="string", nullable=true) 
    */
    protected $auth_token;

    /** 
    * @Column(type="string", nullable=true) 
    */
    protected $reset_token;

    /** 
    * @Column(type="datetime", nullable=true) 
    */
    protected $last_login;


    function getId() {

        return $this->id;

    }

    function setName($name) {
        
        $this->name = $name;

    }

    function getName() {

        return $this->name;

    }

    function setSurname($surname) {

        $this->surname = $surname;

    }

    function getSurname() {

        return $this->surname;

    }

    function setEmail($email) {

        $this->email = $email;

    }

    function getEmail() {

        return $this->email;

    }

    function setPassword($password) {

        $this->password = $password;

    }

    function getPassword() {

        return $this->password;

    }

    function setStatus(AppUserStatus $status) {

        $this->status = $status;
    }

    function getStatus() {

        return $this->status;

    }

    function setAuthToken($auth_token) {

        $this->auth_token = $auth_token;

    }

    function getAuthToken() {

        return $this->auth_token;

    }

    function setRole(AppRole $role) {

        $this->role = $role;

    }

    function getRole() {

        return $this->role;

    }

    function setLastLogin() {

        $this->last_login = new \DateTime(date("Y-m-d H:i"));

    }

    function getLastLogin(){

        return $this->last_login;

    }

    function getFullname() {
        return $this->name . " " . $this->surname;
    }


    /**
     * Get the value of reset_token
     */ 
    public function getResetToken()
    {
        return $this->reset_token;
    }

    /**
     * Set the value of reset_token
     *
     * @return  self
     */ 
    public function setResetToken($reset_token)
    {
        $this->reset_token = $reset_token;

        return $this;
    }
 }