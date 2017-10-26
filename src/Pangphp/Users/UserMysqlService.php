<?php

namespace Pangphp\Users;

use \App\Auth\AuthService as AppAuthService;
use \Doctrine\ORM\EntityManager;

use \App\Users\Entities\User as AppUser;

class UserMysqlService {

	protected $_em;
	protected $_auth;
	protected $_search;
	public $total_items;
	
	function __construct(EntityManager $em, AppAuthService $auth, $search) {
		
		$this->_em = $em;
		$this->_auth = $auth;
		$this->_search = $search;

	}

	function getAllUsers() {
		$qb = $this->_em->createQueryBuilder();
		
		return $qb->select(array("u", "r"))
			->from('App\Users\Entities\User', 'u')
			->innerJoin('u.role', 'r')
			->getQuery()
			->getArrayResult();
	}

	function getPaginatedUsers($page, $limit, $orderby, $direction) {
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

	function getSearchResults($ids, $page, $limit, $orderby, $direction) {
		$qb = $this->_em->createQueryBuilder();
		
		return $qb->select(array("u", "r"))
			->from('App\Users\Entities\User', 'u')
			->where($qb->expr()->in('u.id', ":ids"))
			->setParameter('ids', $ids)
			->orderBy('u.' . $orderby, $direction)
			->innerJoin('u.role', 'r')
			->setFirstResult( ($page - 1) * $limit )
			->setMaxResults( $limit )
			->getQuery()
			->getArrayResult();
	}

	function getUsers($page, $limit, $orderby, $direction, $filter = null) {

		if(isset($filter)) {
			
			$this->_search->search('pangphp', 'users', $filter);
			$this->total_items = $this->_search->total_items;
			return $this->getSearchResults($this->_search->ids, $page, $limit, $orderby, $direction);

		} else  {

			$this->total_items = $this->countAllUsers();
			return $this->getPaginatedUsers($page, $limit, $orderby, $direction);

		}

	}
	
	function getUser($id) {
		$qb = $this->_em->createQueryBuilder();
		
		return $qb->select(array("u","r"))
			->from('App\Users\Entities\User', 'u')
			->innerJoin('u.role', 'r')
			->where('u.id = :id')
			->setParameter('id', $id)
			->getQuery();
	}

	function getUserByEmail($email) {
		$qb = $this->_em->createQueryBuilder();
		
		return $qb->select(array("u"))
			->from('App\Users\Entities\User', 'u')
			->where('u.email = :email')
			->setParameter('email', $email)
			->innerJoin('u.role', 'r')
			->getQuery();
	}

	function countAllUsers() {
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
			// Hash provided password and save it
			$password = $this->_auth->createPassword($user_data["password"]);
		} else {
			$original_password = $this->_auth->createRandomPassword();
			$password = $this->_auth->createPassword($original_password);
		}
		
		$user->setPassword($password);

		$this->_em->persist($user);
		$this->_em->flush();

		return $user;

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
			$user->setStatus($user_data["status"]);
			$user->setRole($role);
			$user->setStatus($status);

			$this->_em->persist($user);
			$this->_em->flush();

			return true;

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