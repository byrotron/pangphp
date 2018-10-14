<?php

namespace Pangphp\Errors\Entities;

class Error {
	
		/**
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue()
	 */
	protected $id;

	/**
	 * @Column(type="integer", nullable=false)
	 */
	protected $line;

	/**
	 * @Column(type="text", nullable=false)
	 */
	protected $file;

	/**
	 * @Column(type="text", nullable=false)
	 */
	protected $trace;
	
	/**
	 * @Column(type="text", nullable=false)
	 */
	protected $message;
	
	/**
	 * @Column(type="datetime", nullable=false)
	 */
	protected $logged_at;
	
	/**
	 * Get the value of id
	 */ 
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Get the value of line
	 */ 
	public function getLine()
	{
		return $this->line;
	}

	/**
	 * Set the value of line
	 *
	 * @return  self
	 */ 
	public function setLine($line)
	{
		$this->line = $line;

		return $this;
  }
  
	/**
	 * Get the value of file
	 */ 
	public function getFile()
	{
		return $this->file;
	}

	/**
	 * Set the value of file
	 *
	 * @return  self
	 */ 
	public function setFile($file)
	{
		$this->file = $file;

		return $this;
	}

	/**
	 * Get the value of trace
	 */ 
	public function getTrace()
	{
		return $this->trace;
	}

	/**
	 * Set the value of trace
	 *
	 * @return  self
	 */ 
	public function setTrace($trace)
	{
		$this->trace = $trace;

		return $this;
	}

	/**
	 * Get the value of message
	 */ 
	public function getMessage()
	{
		return $this->message;
	}

	/**
	 * Set the value of message
	 *
	 * @return  self
	 */ 
	public function setMessage($message)
	{
		$this->message = $message;

		return $this;
	}

	/**
	 * Get the value of logged_at
   * @return DateTime
	 */ 
	public function getLoggedAt()
	{
		return $this->logged_at;
	}

	/**
	 * Set the value of logged_at
	 *
	 * @return  self
	 */ 
	public function setLoggedAt()
	{
		$this->logged_at = new \DateTime();

		return $this;
	}

}