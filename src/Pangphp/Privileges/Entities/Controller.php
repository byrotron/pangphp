<?php


namespace Pangphp\Privileges\Entities;

use \Doctrine\ORM\Mapping as ORM;
use \Doctrine\Common\Collections\ArrayCollection;

use \App\Privileges\Entities\Action as AppAction;

abstract class Controller {

  /**
  * @Id
  * @Column(type="integer")
  * @GeneratedValue(strategy="AUTO")
  */
  protected $id;

  /**
  * @Column(type="string")
  */
  protected $controller;

  /**
  * @Column(type="string")
  */
  protected $label;

  /**
  * @Column(type="text", nullable=true)
  */
  protected $description;

  /**
  * @OneToMany(targetEntity="App\Privileges\Entities\Action", mappedBy="controller")
  */
  protected $actions;

  function __construct() {
    $this->actions = new ArrayCollection();
  }
  function getId() {
    return $this->id;
  }

  function setController($controller) {
    $this->controller = $controller;
  }

  function getController() {
    return $this->controller;
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

  function addActions(AppAction $action) {
    if($this->actions->contains($action)) {
      return;
    }
    $this->actions->add($action);
  }

  function removeActions() {
    $this->actions->remove($action);
  }

  function getActions() {
    return $this->actions;
  }

}