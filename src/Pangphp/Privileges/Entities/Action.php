<?php

namespace Pangphp\Privileges\Entities;

use \Doctrine\ORM\Mapping as ORM;
use \Doctrine\Common\Collections\ArrayCollection;

use \App\Privileges\Entities\Controller as AppController;
use \App\Privileges\Entities\Privilege as AppPrivilege;

abstract class Action {

  /**
  * @Id
  * @Column(type="integer")
  * @GeneratedValue(strategy="AUTO")
  */
  protected $id;

  /**
  * @ManyToOne(targetEntity="App\Privileges\Entities\Controller", inversedBy="actions")
  * @JoinColumn(onDelete="CASCADE")
  */
  protected $controller;

  /**
  * @Column(type="string", length=255)
  */
  protected $action;

  /**
  * @Column(type="string", length=255)
  */
  protected $label;

  /**
  * @Column(type="string", length=255, nullable=true)
  */
  protected $description;

  /**
  * @OneToMany(targetEntity="App\Privileges\Entities\Privilege", mappedBy="action")
  */
  protected $privileges;

  function __construct() {
    $this->privileges = new ArrayCollection();
  }

  function getId() {
    return $this->id;
  }

  function getControllerId() {
    return $this->controller_id;
  }

  function setController(AppController $controller) {
    $this->controller = $controller;
  }

  function getController() {
    return $this->controller;
  }

  function setAction($action) {
    $this->action = $action;
  }

  function getAction() {
    return $this->action;
  }

  function setLabel($label) {
    $this->label = $label;
  }

  function getLabel() {
    return $this->label;
  }

  function setDescription($description) {
    $this->description = $description;
  }

  function getDescription() {
    return $this->description;
  }

  function addPrivilege(AppPrivilege $privilege) {
    $this->privileges->add($privilege);
  }

  function removePrivilege(AppPrivilege $privilege) {
    $this->privileges->remove($privilege);
  }

  function getPrivileges() {
    return $this->privileges;
  }

}