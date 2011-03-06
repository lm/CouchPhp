<?php

namespace CouchPhp;

use OutOfBoundsException;



/**
 * Query result set.
 */
class QueryResult extends Object
{
	private $rows;



	public function __construct(array $rows)
	{
		$this->rows = $rows;
	}



	/**
	 * Fetches first row.
	 * @return stdClass|NULL
	 */
	public function fetch()
	{
		return empty($this->rows) ? NULL : current($this->rows);
	}



	/**
	 * Fetches rows
	 * @return array
	 */
	public function fetchRows()
	{
		$rows = array();
		foreach ($this->rows as $row) {
			if (isset($row->error)) {
				$rows[] = NULL;
			} else {
				$rows[] = $row;
			}
		}
		return $rows;
	}



	/**
	 * Fetchs only docs
	 * @return array
	 * @throws OutOfBoundsException
	 */
	public function fetchDocs()
	{
		$docs = array();
		foreach ($this->rows as $row) {
			if (isset($row->error)) {
				$docs[] = NULL;
			} elseif (!isset($row->doc)) {
				throw new OutOfBoundsException('Cannot fetch docs because bulk document was not queried with include_docs=true');
			} else {
				$doc = $row->doc;
				$doc->_key = $row->key;
				$doc->_value = $row->value;
				$docs[] = $doc;
			}
		}
		return $docs;
	}



	/**
	 * Fetchs only values
	 * @return array
	 */
	public function fetchValues()
	{
		$values = array();
		foreach ($this->rows as $row) {
			if (isset($row->error)) {
				$values[] = NULL;
			} else {
				$values[] = $row->value;
			}
		}
		return $values;
	}
}