<?php

namespace Pangphp\EditableLists\Entities;

use \Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity
 * @Table(name="editable_lists", uniqueConstraints={@UniqueConstraint(name="unique_name", columns={"name", "group_id"})})
 */
class EditableList {

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
   * @OneToMany(targetEntity="Pangphp\EditableLists\Entities\EditableListItem", mappedBy="list")
   */
  protected $items;

  /**
   * @ManyToOne(targetEntity="Pangphp\EditableLists\Entities\EditableListGroup", inversedBy="lists") 
   * @JoinColumn(onDelete="Cascade")
   */
  protected $group;

  function __construct() {
    $this->items = new ArrayCollection();
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

  function addItems($item) {
    $this->items->add($item);
  }

  function removeItems(EditableListItem $item) {
    $this->items->remove($item);
  }

  function getItems() {
    return $this->items;
  }

  function setGroup(EditableListGroup $group) {
    $this->group = $group;
  }

  function getGroup() {
    return $this->group;
  }

}