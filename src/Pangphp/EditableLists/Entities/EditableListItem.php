<?php

namespace Pangphp\EditableLists\Entities;
use \Pangphp\EditableList\Entities\EditableLists;

/**
 * @Entity
 * @Table(name="editable_list_items", uniqueConstraints={@UniqueConstraint(name="unique_list_item", columns={"list_id", "value"})})
 */
class EditableListItem {

  /**
  * @id
  * @Column(type="integer")
  * @GeneratedValue
  */
  protected $id;

  /**
   * @ManyToOne(targetEntity="Pangphp\EditableLists\Entities\EditableList", inversedBy="items") 
   * @JoinColumn(onDelete="Cascade")
   */
  protected $list;

  /**
   *  @Column(type="string", nullable=false) 
   */
  protected $value;

   /**
   *  @Column(type="boolean", nullable=false) 
   */
  protected $status = true;

  function getId() {
    return $this->id;
  }
  
  function setValue($value) {
    $this->value = $value;
  }

  function getValue() {
    return $this->value;
  }

  function setStatus($status) {
    $this->status = $status;
  }

  function getStatus() {
    return $this->status;
  }

  function setList(EditableList $list) {
    $this->list = $list;
  }

  function getList() {
    return $this->list;
  }
}