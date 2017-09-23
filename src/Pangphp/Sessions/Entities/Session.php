<?php

namespace Pangphp\Sessions\Entities;

 /**
 * @Entity
 * @Table(name="sessions")
 */
class Session {

  /**
  * @id
  * @GeneratedValue
  * @Column(type="integer", nullable=false)
  */
  protected $id;

  /**
  * @Column(type="string", unique=true, nullable=false)
  */
  protected $session_id;

  /**
  * @Column(type="text", nullable=true)
  */
  protected $data = NULL;

  /**
  * @Column(type="datetime", nullable=false)
  */
  protected $last_accessed;

  function getId() {
    return $this->id;
  }

  function setSessionId($session_id) {
    $this->session_id = $session_id;
  }

   function getSessionID() {
    $this->session_id;
  }

  function setData($data) {
    $this->data = $data;
  }

  function getData() {
    return $this->data;
  }

  function setLastAccessed() {
    $this->last_accessed = New \DateTime(date("Y-m-d H:i:s"));
  }

  function getLastAccessed() {
    if(isset($this->last_accessed)) {
      return new DateTime($this->last_accessed);
    } else {
      return false;
    }
  }

}