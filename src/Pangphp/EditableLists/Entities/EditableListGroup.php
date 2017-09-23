<?php

namespace Pangphp\EditableLists\Entities;

use \Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity
 * @Table(name="editable_list_groups", uniqueConstraints={@UniqueConstraint(name="unique_group", columns={"name"})})
 */
class EditableListGroup {

/**
  * @id
  * @Column(type="integer")
  * @GeneratedValue
  */
  protected $id;

  /**
   * @Column(type="string") 
   */
  protected $name;

  /**
   * @OneToMany(targetEntity="Pangphp\EditableLists\Entities\EditableList", mappedBy="group")
   */
  protected $lists;

  function __construct() {
    $this->lists = new ArrayCollection();
  }

  function getId() {
    return $this->id;
  }

  function setName($name) {
    $this->name = $name;
  }

  function getName() {
    return $this->name;
  }

  function getLists() {
    return $this->lists;
  }
}