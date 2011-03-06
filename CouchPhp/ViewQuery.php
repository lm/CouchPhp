<?php

namespace CouchPhp;



/**
 * View query builder.
 *
 * @property-read Design $design
 */
class ViewQuery extends BaseBulkDocument
{
	/** @var string */
	private $name;

	/** @var Design */
	private $design;



	/**
	 * @param string
	 * @param Design
	 */
	public function __construct($name, Design $design)
	{
		parent::__construct("_design/{$design->name}/_view/{$name}", $design->getDatabase());
		$this->name = $name;
		$this->design = $design;
	}



	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}



	/**
	 * @return Design
	 */
	public function getDesign()
	{
		return $this->design;
	}
}