<?php

namespace Pangphp\Roles\Entities;

use \Doctrine\Common\Collections\ArrayCollection;
use  \App\Privileges\Entities\Privilege as AppPrivilege;

abstract class Role {

  /**
  * @id
  * @Column(type="integer")
  * @GeneratedValue
  */
  protected $id;

  /** 
  * @Column(type="string", nullable=false, ) 
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

  // Set default and not able to be set by anyone besides DBA
  function setEnabled() {
    $this->enabled = true;
  }

  function getEnabled() {
    return $this->enabled;
  }


  function getPrivileges() {
    return $this->privileges;
  }

  function get_array() {

    $array = get_object_vars($this);
    return $array;

  }

}

?>