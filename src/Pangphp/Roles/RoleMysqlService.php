<?php

namespace Pangphp\Roles;

use \Doctrine\ORM\EntityManager;
use \App\Privileges\PrivilegeMysqlService as AppPrivilegeMysqlService;
use \App\Roles\Entities\Role as AppRole;

class RoleMysqlService {
	
	protected $_entity_manager;
	protected $_privilege;

	function __construct(AppPrivilegeMysqlService $privilege, EntityManager $entity_manager) {

			$this->_entity_manager = $entity_manager;
			$this->_privilege = $privilege;

	}

	function create_role($name) {

		$role = new AppRole();
		$role->setName($name);
		$role->setEnabled();

		$this->_entity_manager->persist($role);
		$this->_entity_manager->flush();

		$this->_privilege->generate();

		return $role;

	}

	function get_roles() {

		$qb = $this->_entity_manager->createQueryBuilder();

		return $qb->select(array("r"))
							->from('App\Roles\Entities\Role', 'r')
							->orderBy('r.name', 'ASC')
							->getQuery()
							->getArrayResult();

	}
	
	function update_role($id, $name) {

		$role = $this->_entity_manager->find('App\Roles\Entities\Role', $id);

		if($role) {
			$role->setName($name);
			$role->setEnabled();

			$this->_entity_manager->persist($role);
			$this->_entity_manager->flush();

			$this->_privilege->generate();

			return true;
		}

		return false;

	}
	
	function delete_role($id) {

		$role = $this->_entity_manager->find('App\Roles\Entities\Role', $id);

		if($role) {

			$this->_entity_manager->remove($role);
			$this->_entity_manager->flush();

			return true;
			
		}

		return false;

	}

}
