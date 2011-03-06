<?php

namespace CouchPhp;



/**
 * Represents design document.
 *
 * @property-read string $name
 * @property-read Database $database
 */
class Design extends Object
{
	/** @var string */
	private $name;

	/** @var Database */
	private $database;



	/**
	 * @param string
	 * @param Database
	 */
	public function __construct($name, Database $database)
	{
		$this->name = $name;
		$this->database = $database;
	}



	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}



	/**
	 * @return Database
	 */
	public function getDatabase()
	{
		return $this->database;
	}



	/********************* view *********************/



	/**
	 * @param  string
	 * @return ViewQuery
	 */
	public function queryView($name)
	{
		return new ViewQuery($name, $this);
	}
}