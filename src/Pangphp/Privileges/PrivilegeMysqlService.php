<?php

namespace Pangphp\Privileges;

use \Doctrine\ORM\EntityManager;

use \App\Users\Entities\User as AppUser;
use \App\Privileges\Entities\Privilege as AppPrivilege;
use \App\Roles\Entities\Role as AppRole;

abstract class PrivilegeMysqlService {

	protected $_entity_manager;

	CONST ACCESS_DENIED = 'You are not authorised to complete this action';

	function __construct(EntityManager $em) {
		$this->_entity_manager = $em;
	}

	function getControllers() {

		$qb = $this->_entity_manager->createQueryBuilder();

		return $qb->select(array("c", "a"))
							->from('App\Privileges\Entities\Controller', 'c')
							->leftJoin('c.actions', 'a')
							->getQuery();
	}

	function updatePrivilege($action, $role, $status) {

		$qb = $this->_entity_manager->createQueryBuilder();

		$privilege = $qb->select(array("p"))
										->from('App\Privileges\Entities\Privilege', 'p')
										->where('p.action = :action AND p.role = :role')
										->setParameters(array(
											'action' => $action,
											'role' => $role
										))
										->getQuery()
										->getOneOrNullResult();
		
		// Dont update it if it is the same value as what is in the database currently
		if($privilege->getStatus() !== $status) {
			$privilege->setStatus($status);
			
			$this->_entity_manager->persist($privilege);
			$this->_entity_manager->flush();
		}

	}

	function getActions() {

		$qb = $this->_entity_manager->createQueryBuilder();

		return $qb->select(array("a"))
							->from('App\Privileges\Entities\Action', 'a')
							->getQuery();
	}

	function getRoles() {

		$qb = $this->_entity_manager->createQueryBuilder();

		return $qb->select(array("r"))
							->from('App\Roles\Entities\Role', 'r')
							->where('r.id > 1')
							->getQuery();
	}

	function getPrivileges() {

		$qb = $this->_entity_manager->createQueryBuilder();

		return $qb->select(array("p.id, p.status, a.id as action_id, r.id as role_id"))
							->from('App\Privileges\Entities\Privilege', 'p')
							->leftJoin('p.action', 'a')
							->leftJoin('p.role', 'r')
							->getQuery();
	}

	function getRolePrivileges(AppRole $role) {

		$qb = $this->_entity_manager->createQueryBuilder();

		return $qb->select(array("a.action, p.status"))
							->from('App\Privileges\Entities\Privilege', 'p')
							->innerJoin('p.action', 'a')
							->innerJoin('p.role', 'r')
							->where('p.role = :role')
							->setParameter("role", $role->getId())
							->getQuery()
							->getArrayResult();
	}

	public function privilegeExists($action_id, $role_id) {

		$qb = $this->_entity_manager->createQueryBuilder();

		return $qb->select(array("count(p.id)"))
							->from('App\Privileges\Entities\Privilege', 'p')
							->leftJoin('p.action', 'a')
							->leftJoin('p.role', 'r')
							->where('a.id = :action AND r.id = :role')
							->setParameters(array(
								"action" => $action_id,
								"role" => $role_id
							))
							->getQuery();
	}

	protected function getAction($controller_name, $action_name) {

		$qb = $this->_entity_manager->createQueryBuilder();

		// var_dump($controller_name);
		// var_dump($action_name);
		// exit;

		return $qb->select(array('a'))
							->from('App\Privileges\Entities\Action', 'a')
							->innerJoin('a.controller', 'c')
							->where('a.action = :action AND c.controller = :controller')
							->setParameters(array(
								'action' => $action_name,
								'controller' => $controller_name
							))
							->getQuery()
							->getOneOrNullResult();
	}

	protected function getProtectedAction($controller, $action, AppUser $user) {
		$action = $this->getAction($controller, $action);
		$role = $user->getRole();

		$qb = $this->_entity_manager->createQueryBuilder();
		
		return $qb->select('p.status')
							->from('App\Privileges\Entities\Privilege', 'p')
							->leftJoin('p.action', 'a')
							->leftJoin('p.role', 'r')
							->where('a.id = :action AND r.id = :role')
							->setParameters(array(
								"action" => $action->getId(),
								"role" => $role->getId()
							))
							->getQuery()
							->getOneOrNullResult();
	}

	public function isSuperUser(AppUser $user) {
		$role = $user->getRole();
		return $role->getId() === 1 ? true : false;
	}

	public function onlySuperUser(AppUser $user) {

		if($this->isSuperUser($user) === true) {
			throw new PrivilegeException(self::ACCESS_DENIED);
		}

		return true;
	}

	public function protectedAction($controller, $action, AppUser $user) {

		// Check first if the user is a super user, if not then check privileges
		if($this->isSuperUser($user) === false) {

			$result = $this->getProtectedAction($controller, $action, $user);

			if($result["status"] === false) {
				throw new PrivilegeException(self::ACCESS_DENIED);
				return false;
			}

		}

		return true;

	}

	public function generate() {

		$actions = $this->getActions()->getResult();
		$roles = $this->getRoles()->getResult();

		foreach($actions as $action) {

			foreach($roles as $role) {
			 
				// Dont insert for the super user as this will have access to the entire system
				if($role->getId() !== 1) {

					// Does an entry exist with a role and access control
					$item = $this->privilegeExists($action->getId(), $role->getId())->getSingleScalarResult();
					
					if($item === 0) {
						$new_item = new AppPrivilege();
						$new_item->setStatus(0);
						$new_item->setRole($role);
						$new_item->setAction($action);
						
						$this->_entity_manager->persist($new_item);
						$this->_entity_manager->flush();

					}
				
				}

			}

		}
		
	}

}

?>

