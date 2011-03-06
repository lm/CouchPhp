<?php

namespace CouchPhp;

use	RuntimeException;



/**
 * Query builder.
 *
 * @property-read Database $database
 */
abstract class BaseBulkDocument extends Object
{
	/** @var string */
	private $path;

	/** @var Database */
	private $database;

	/** @var array */
	protected $query = array();

	/** @var array */
	protected $postData = array();



	/**
	 * @param string
	 * @param Database
	 */
	public function __construct($path, Database $database)
	{
		$this->path = $path;
		$this->database = $database;
	}



	/**
	 * @return Database
	 */
	public function getDatabase()
	{
		return $this->database;
	}



	/********************* specification *********************/



	/**
	 * @param  mixed
	 * @return BaseBulkDocument
	 */
	public function key($key)
	{
		if (func_num_args() > 1) {
			$key = func_get_args();
		}
		$this->query['key'] = Json::encode($key);
		return $this;
	}



	/**
	 * @param  array	of mixed values
	 * @return BaseBulkDocument
	 */
	public function keys($keys)
	{
		$this->postData['keys'] = (array) $keys;
		return $this;
	}



	/**
	 * @param  mixed
	 * @return BaseBulkDocument
	 */
	public function startKey($key)
	{
		if (func_num_args() > 1) {
			$key = func_get_args();
		}
		$this->query['startkey'] = Json::encode($key);
		return $this;
	}



	/**
	 * @param  mixed
	 * @return BaseBulkDocument
	 */
	public function endKey($key)
	{
		if (func_num_args() > 1) {
			$key = func_get_args();
		}
		$this->query['endkey'] = Json::encode($key);
		return $this;
	}



	/**
	 * @param  mixed
	 * @return BaseBulkDocument
	 */
	public function startId($key)
	{
		if (func_num_args() > 1) {
			$key = func_get_args();
		}
		$this->query['startkey_docid'] = Json::encode($key);
		return $this;
	}



	/**
	 * @param  mixed
	 * @return BaseBulkDocument
	 */
	public function endId($key)
	{
		if (func_num_args() > 1) {
			$key = func_get_args();
		}
		$this->query['endkey_docid'] = Json::encode($key);
		return $this;
	}



	/**
	 * Switch to descending sorting.
	 * @param  bool
	 * @return BaseBulkDocument
	 */
	public function desc($desc = TRUE)
	{
		$this->query['descending'] = $desc ? 'true' : 'false';
		return $this;
	}



	/**
	 * @param  int
	 * @return BaseBulkDocument
	 */
	public function skip($count)
	{
		$this->query['skip'] = (int) $count;
		return $this;
	}



	/**
	 * Limit count of fetchted documents.
	 * @param  int
	 * @return BaseBulkDocument
	 */
	public function limit($limit)
	{
		$this->query['limit'] = (int) $limit;
		return $this;
	}



	/**
	 * Includes full documents in result.
	 * @param  bool
	 * @return BaseBulkDocument
	 */
	public function includeDocs($include = TRUE)
	{
		$this->query['include_docs'] = $include ? 'true' : 'false';
		return $this;
	}



	/********************* reduce *********************/



	/**
	 * @param  NULL|bool|int
	 * @return BaseBulkDocument
	 */
	public function reduce($level = NULL)
	{
		$this->query['reduce'] = $level === FALSE ? 'false' : 'true';
		if ($level === TRUE) {
			$this->query['group'] = 'true';
		} elseif (is_numeric($level)) {
			$this->query['group_level'] = $level;
		}
		return $this;
	}



	/********************* fetching *********************/



	/**
	 * @return QueryResult
	 * @throws RuntimeException
	 */
	public function query()
	{
		$result = $this->database->makeRequest(
			!empty($this->postData) ? 'POST' : 'GET',
			$this->path,
			$this->query,
			!empty($this->postData) ? Json::encode($this->postData) : NULL
		);

		if ($result === NULL) {
			throw new RuntimeException("Database '{$this->database->name}' has no bulk document '{$this->path}'");
		}

		return new QueryResult($result->rows);
	}



	/**
	 * @return array
	 */
	public function fetchRows()
	{
		return $this->query()->fetchRows();
	}



	/**
	 * @return array
	 */
	public function fetchDocs()
	{
		return $this->includeDocs()->query()->fetchDocs();
	}



	/**
	 * @return array
	 */
	public function fetchValues()
	{
		return $this->query()->fetchValues();
	}
}