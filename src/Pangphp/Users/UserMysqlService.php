<?php

namespace Pangphp\Users;

use \App\Auth\AuthService as AppAuthService;
use \Pangphp\Mail\MailService;
use \Doctrine\ORM\EntityManager;

use \App\Users\Entities\User as AppUser;

class UserMysqlService {

	/**
	 * @var EntityManager $_em;
	 */
	protected $_em;

	/**
	 * @var AppAuthService $_aut
	 */
	protected $_auth;

	/**
	 * @var MailService $_mailer
	 */
	protected $_mailer;

	public $total_items;
	
	function __construct(EntityManager $em, AppAuthService $auth, MailService $mailer) {
		
		$this->_em = $em;
		$this->_auth = $auth;
		$this->_mailer = $mailer;

	}

	function getAllUsers() {
		$qb = $this->_em->createQueryBuilder();
		
		return $qb->select(array("u", "r"))
			->from('App\Users\Entities\User', 'u')
			->innerJoin('u.role', 'r')
			->getQuery()
			->getArrayResult();
	}

	function getUsers($page, $limit, $orderby, $direction, $filter = null) {

		$qb = $this->_em->createQueryBuilder();
		
		return 	$qb->select(array("u", "r", "s"))
			->from('App\Users\Entities\User', 'u')
			->orderBy('u.' . $orderby, $direction)
			->innerJoin('u.role', 'r')
			->innerJoin('u.status', 's')
			->setFirstResult( ($page - 1) * $limit )
			->setMaxResults( $limit )
			->getQuery()
			->getArrayResult();

	}
	
	function getUser($id) {
		$qb = $this->_em->createQueryBuilder();
		
		return $qb->select(array("u","r", "s"))
			->from('App\Users\Entities\User', 'u')
			->innerJoin('u.role', 'r')
			->innerJoin('u.status', 's')
			->where('u.id = :id')
			->setParameter('id', $id)
			->getQuery();
	}

	function getUserByEmail($email) {
		$qb = $this->_em->createQueryBuilder();
		
		return $qb->select(array("u", "r", "s"))
			->from('App\Users\Entities\User', 'u')
			->where('u.email = :email')
			->setParameter('email', $email)
			->innerJoin('u.role', 'r')
			->innerJoin('u.status', 's')
			->getQuery();
	}

	function getUserByToken($token) {
		$qb = $this->_em->createQueryBuilder();
		
		return $qb->select(array("u"))
			->from('App\Users\Entities\User', 'u')
			->where('u.reset_token = :token')
			->setParameter('token', $token)
			->getQuery();
	}

	function totalUsers() {
		$qb = $this->_em->createQueryBuilder();

		return $qb->select(array("COUNT(u.id)"))
			->from('App\Users\Entities\User', 'u')
			->getQuery()
			->getSingleScalarResult();
	}

	function createUser($user_data) {
		
		$role = $this->_em->find('App\Roles\Entities\Role', $user_data["role"]);
		$status = $this->_em->find('App\Users\Entities\UserStatus', $user_data["status"]);
		
		$user = new AppUser();
		$user->setName($user_data["name"]);
		$user->setSurname($user_data["surname"]);
		$user->setEmail($user_data["email"]);
		$user->setRole($role);
		$user->setStatus($status);
		
		if(isset($user_data['password'])){
			// Hash provided password and save it\
			$original_password = $user_data["password"];
		} else {
			$original_password = $this->_auth->createRandomPassword();
		}

		$password = $this->_auth->createPassword($original_password);
		
		$user->setPassword($password);
		$this->_em->persist($user);
		$this->_em->flush();

		$this->newUserNotification($user, $original_password);

		$result = $this->getUserByEmail($user->getEmail())->getArrayResult()[0];
		unset($result["password"]);
		return $result;

	}

	function sendResetLink($email, $link) {
		$user = $this->getUserByEmail($email)->getOneOrNullResult();

		if(!$user) {
			throw new \Pangphp\Auth\AuthException("We could not find this email in our database. Please check the spelling and try again? If this continues please contact you adminstrator?");
		}

		$user->setResetToken($link);

		$this->_mailer->sendFromAdmin();

    $this->_mailer->setTo([
      ["name" => $user->getFullname(), "email" => $user->getEmail()]
    ]);
    $this->_mailer->setTemplate(dirname(__FILE__) . '/Emails/reset-account.notification.php');
    $this->_mailer->setSubject("Account Reset");
    $this->_mailer->setData([
			"user" => $user,
			"link" => $link
    ]);
    $this->_mailer->sendmail();

		
    $this->_em->persist($user);
    $this->_em->flush();

	}

	function resetPassword($token, $password) {
		$user = $this->getUserByToken($token)->getOneOrNullResult();

    if(!$user) {
      throw new \Pangphp\Auth\AuthException('This token is not valid');
		}
		
		$user->setPassword($password);
		$user->setResetToken(NULL);
		
		$this->_em->persist($user);
		$this->_em->flush();

	}
		
	function newUserNotification(\Pangphp\Users\Entities\User $user, $password) {
		throw new \Exception("New User notification has not been setup");
	}

	function registerUser($user_data) {
	
		$user = $this->_em->getRepository("App\Users\Entities\User")
			->findOneBy(array(
				"email" => $user_data["email"]
			));
		
		if(is_null($user)) {
			
			$this->createUser($user_data);
			
		} else {
			throw new \Exception("This email already exists");
		}
		
	}
	
	function updateUser($id, $user_data) {
		
		$user = $this->_em->find("App\Users\Entities\User", $id);

		if($user) {
			
			$role = $this->_em->find('App\Roles\Entities\Role', $user_data["role"]);
			$status = $this->_em->find('App\Users\Entities\UserStatus', $user_data["status"]);
			
			$user->setName($user_data["name"]);
			$user->setSurname($user_data["surname"]);
			$user->setEmail($user_data["email"]);
			$user->setRole($role);
			$user->setStatus($status);

			$this->_em->persist($user);
			$this->_em->flush();

			$result = $this->getUser($user->getId());
			return $user->getId();

		} else {

			return false;

		}

	}

	function deleteUser($id) {

		$user = $this->_em->find("App\Users\Entities\User", $id);
		$this->_em->remove($user);
		$this->_em->flush();

	}

}