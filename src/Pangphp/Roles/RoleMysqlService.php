<?php

namespace Pangphp\Roles;

use \Doctrine\ORM\EntityManager;
use \App\Privileges\PrivilegeMysqlService as AppPrivilegeMysqlService;
use \App\Roles\Entities\Role as AppRole;

class RoleMysqlService {
	
	protected $_em;
	protected $_privilege;

	function __construct(AppPrivilegeMysqlService $privilege, EntityManager $em) {

			$this->_em = $em;
			$this->_privilege = $privilege;

	}

	function getRole($id) {
		$qb = $this->_em->createQueryBuilder();
		
		return $qb->select(array("r"))
			->from('App\Roles\Entities\Role', 'r')
			->where('r.id = :id')
			->setParameter('id', $id)
			->getQuery();
	}

	function createRole($name) {

		$role = new AppRole();
		$role->setName($name);
		$role->setEnabled();

		$this->_em->persist($role);
		$this->_em->flush();

		$this->_privilege->generate();

		return $this->getRole($role->getId())->getArrayResult()[0];

	}

	function getRoles() {

		$qb = $this->_em->createQueryBuilder();

		return $qb->select(array("r"))
			->from('App\Roles\Entities\Role', 'r')
			->orderBy('r.name', 'ASC')
			->getQuery()
			->getArrayResult();

	}
	
	function updateRole($id, $name) {

		$role = $this->_em->find('App\Roles\Entities\Role', $id);

		if($role) {
			$role->setName($name);
			$role->setEnabled();

			$this->_em->persist($role);
			$this->_em->flush();

			$this->_privilege->generate();

			return true;
		}

		return false;

	}
	
	function deleteRole($id) {

		$role = $this->_em->find('App\Roles\Entities\Role', $id);

		if($role) {

			$this->_em->remove($role);
			$this->_em->flush();

			return true;
			
		}

		return false;

	}

}
