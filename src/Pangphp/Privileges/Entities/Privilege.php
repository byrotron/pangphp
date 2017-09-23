<?php  

namespace Pangphp\Privileges\Entities;

use \Doctrine\ORM\Mapping as ORM;
use \Doctrine\Common\Collections\ArrayCollection;

use \App\Privileges\Entities\Action as AppAction;
use \App\Roles\Entities\Role as AppRole;

abstract class Privilege {

  /**
  * @Id
  * @Column(type="integer")
  * @GeneratedValue(strategy="AUTO")
  */
  protected $id;

  /**
  * @ManyToOne(targetEntity="App\Privileges\Entities\Action", inversedBy="privileges", cascade={"remove"})
  * @JoinColumn(name="action_id", referencedColumnName="id", onDelete="Cascade")
  */
  protected $action;
  
  /**
  * @ManyToOne(targetEntity="App\Roles\Entities\Role", inversedBy="privileges", cascade={"remove"})
  * @JoinColumn(name="role_id", referencedColumnName="id", onDelete="Cascade")
  */
  protected $role;

  /**
  * @Column(type="boolean")
  */
  protected $status;

  function getId() {
    return $this->id;
  }

  function setAction(AppAction $action) {
    $this->action = $action;
  }

  function getAction() {
    return $this->action;
  }

  function setRole(AppRole $role) {
    $this->role = $role;
  }

  function getRole() {
    return $this->roles;
  }

  function setStatus($status) {
    $this->status = $status;
  }

  function getStatus() {
    return $this->status;
  }

}
  
