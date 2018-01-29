<?php

namespace Pangphp\Roles\Entities;

use \Doctrine\Common\Collections\ArrayCollection;
use  \App\Privileges\Entities\Privilege as AppPrivilege;

abstract class Role {

  /**
  * @id
  * @Column(type="integer")
  * @GeneratedValue(strategy="AUTO")
  */
  protected $id;

  /** 
  * @Column(type="string", nullable=false) 
  */
  protected $name;

   /** 
  * @Column(type="boolean", nullable=false) 
  */
  protected $enabled;

  /**
   * @OneToMany(targetEntity="App\Privileges\Entities\Privilege", mappedBy="role")
   */
  protected $privileges;

  function getId() {
    return $this->id;
  }

  function setName($name) {
    $this->name = $name;
  }

  function getName() {
    return $this->name;
  }

  function setEnabled() {
    $this->enabled = true;
  }

  function getEnabled() {
    return $this->enabled;
  }


  function getPrivileges() {
    return $this->privileges;
  }

}

?>