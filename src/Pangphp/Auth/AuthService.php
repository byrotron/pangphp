<?php

namespace Pangphp\Auth;

use \Doctrine\ORM\EntityManager;
use \Pangphp\Strings\StringService;
use \App\Users\Entities\User;

class AuthService {
		
		protected $_em;
		protected $_str;

		public $current_user;

		const NOT_AUTHD = 'You are not authenticated and therefore are not authorised to complete this action';
		const BLOCKED = 'Your account has been disabled, please contact your system adminstrator';
		const CREDENTIALS = 'Your credentials are not correct';

		function __construct(EntityManager $em, StringService $str) {
				$this->_em = $em;
				$this->_str = $str;
				
				if(CRYPT_BLOWFISH) {
						$this->_prefix = "$2y$";
						$this->_size = 22;
						$this->_salt = $this->_str->randomStringGenerator($this->_size) . "$";
						$this->_type = "blowfish";
						return;
				} else if(CRYPT_SHA512) {
						$this->_prefix = "$6$";
						$this->_size = 16;
						$this->_salt = $this->_str->randomStringGenerator($this->_size) . "$";
						$this->_type = "sha512";
						return;
				} else if(CRYPT_SHA256) {
						$this->_prefix = "$5$";
						$this->_size = 16;
						$this->_salt = $this->_str->randomStringGenerator($this->_size) . "$";
						$this->_type = "sha256";
						return;
				} else if(CRYPT_MD5) {
						$this->_prefix = "$1$";
						$this->_size = 12;
						$this->_salt = $this->_str->generate_random_str($this->_size) . "$";
						$this->_type = "md5";
						return;
				}

		}

		protected function setCost() {

				if($this->_type == "blowfish") {
						return 14 . "$";
				} else if($this->_type == "md5") {
						return "";
				} else if(strpos($this->_type, "sha")) {
						return "rounds=2500000$";
				}

		}

		protected function setSalt() {
				return $this->_prefix . $this->setCost() .  $this->_salt;
		}

		function createRandomPassword($digits = 8) {
				return $this->_str->randomStringGenerator($digits);
		}
		
		function createPassword($password) {
				return crypt($password, $this->setSalt());
		}

		function verifyPassword($password, $hash) {
				//Seperate salt from password
				if(crypt($password, $this->getSalt($hash)) === $hash) {
						return true;
				} else {
						return false;
				}
		}

		function getStr($hash) {
				return substr($hash, $this->_size + (strlen($this->_prefix) + strlen($this->setCost())));
		}

		function getSalt($hash) {
				return substr($hash, 0, $this->_size + (strlen($this->_prefix) + strlen($this->setCost())));
		}

		function login($password, User $user) {
				
				if($this->verifyPassword($password, $user->getPassword()) === true) {
					 
						$this->isActiveUser($user);
		
						$auth_token = $this->getAuthToken();
						$_SESSION["auth_token"] =  $auth_token;
		
						$user->setAuthToken($auth_token);
						$user->setLastLogin();
		
						$this->_em->persist($user);
						$this->_em->flush();
					
						return true;
				} else {
						throw new AuthException(self::CREDENTIALS);
				}

		}

		function isActiveUser(User $user) {

				if($user->getStatus()->getId() === 1) {
						return true;
				} else {
						throw new AuthException(self::BLOCKED);
				}

		}

		function getAuthToken($size = 32) {
				return md5($this->_str->randomStringGenerator($size));
		}
		
		function getAuthdUser() {
				
				if(isset($_SESSION["auth_token"])) {
					
					$qb = $this->_em->createQueryBuilder();
					$user = $qb->select(array("u", "r", "s"))
										 ->from('App\Users\Entities\User', 'u')
										 ->innerJoin('u.role', 'r')
										 ->innerJoin('u.status', 's')
										 ->where('u.auth_token = :token')
										 ->setParameters(array(
										 	'token' => $_SESSION["auth_token"]
										 ));

					$query = $qb->getQuery();
					$user = $query->getSingleResult();
					$this->current_user = $query->getArrayResult()[0];
	
					return $user instanceof \App\Users\Entities\User ? $user : false;
						
				} else {
						return false;
				}
		}

		function isAuthd() {
			
			if(!is_object($user = $this->getAuthdUser())){
				throw new AuthException(self::NOT_AUTHD);
			}
		
			$auth_token = $user->getAuthToken();
			
			// Confirm the user has not been blocked
			$this->isActiveUser($user);
	
			if(!isset($auth_token) && $auth_token !== $_SESSION["auth_token"]){
				throw new AuthException(self::NOT_AUTHD);
			}
	
			return true;
		}

		function logout($auth_token) {
	
			$user = $this->_em->getRepository("App\Users\Entities\User")
					->findOneBy(array(
							"auth_token"=>$auth_token
					));
			$user->setAuthToken(NULL);
			$this->_em->persist($user);
			$this->_em->flush();
	
			session_destroy();
		}

}