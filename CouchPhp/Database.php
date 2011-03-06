<?php

namespace CouchPhp;

use Traversable,
	InvalidArgumentException;



/**
 * Represents CouchDB database.
 *
 * @property-read Connection $connection
 * @property-read string $name
 * @property-read BulkDocumnet $bulkDocument
 */
class Database extends Object
{
	const REVS = 1,
		REVS_INFO = 2;

	/** @var Connection */
	private $connection;

	/** @var string */
	private $name;

	/** @var array of stored documents revisions */
	private $revs = array();



	/**
	 * @param Connection	couchdb connection
	 * @param string		database name
	 */
	public function __construct(Connection $connection, $name)
	{
		$this->connection = $connection;
		$this->name = $name;
	}



	/**
	 * @return Connection
	 */
	public function getConnection()
	{
		return $this->connection;
	}



	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}



	/********************* documents *********************/



	/**
	 * Load document. If document doesn't exists, returns NULL.
	 * @param  string
	 * @param  string
	 * @param  int
	 * @return stdClass|NULL
	 */
	public function load($id, $rev = NULL, $flags = NULL)
	{
		$query = array();
		$flags & self::REVS && $query['revs'] = 'true';
		$flags & self::REVS_INFO && $query['revs_info'] = 'true';
		$rev && $query['rev'] = $rev;
		return $this->makeRequest('GET', rawurlencode($id), $query);
	}



	/**
	 * Save document.
	 * @param  array|object
	 * @return stdClass
	 */
	public function save($doc)
	{
		$doc = $this->prepareDocument($doc, FALSE);
		$method = 'POST';
		$path = '';
		if (isset($doc->_id)) {
			$method = 'PUT';
			$path = rawurlencode($doc->_id);
		}
		return $this->makeRequest($method, $path, NULL, Json::encode($doc));
	}



	/**
	 * Delete document.
	 * @param  string
	 * @return stdClass
	 */
	public function delete($id, $rev = NULL)
	{
		$doc = $this->prepareStub($id, $rev === NULL);
		$rev !== NULL && $doc->_rev = $rev;
		return $this->makeRequest('DELETE', rawurlencode($doc->_id), array('rev' => $doc->_rev));
	}



	/**
	 * Prepare document to store (set _rev and convert to stdClass).
	 * @param  array|object
	 * @param  bool
	 * @return stdClass
	 * @throws InvalidArgumentException
	 */
	public function prepareDocument($doc, $needRev = TRUE)
	{
		if ($doc instanceof Traversable) {
			$doc = iterator_to_array($doc);
		} elseif (!is_array($doc) && !is_object($doc)) {
			throw new InvalidArgumentException('Document must be array or object');
		}

		$doc = (object) $doc;
		if (!isset($doc->_rev) && isset($doc->_id)) {
			if (isset($this->revs[$doc->_id])) {
				$doc->_rev = $this->revs[$doc->_id];
			} elseif ($needRev) {
				throw new InvalidArgumentException("Unknown revision for document '$doc->_id'");
			}
		}
		return $doc;
	}



	private function prepareStub($doc, $needRev = TRUE)
	{
		if (is_scalar($doc)) {
			$doc = array('_id' => $doc);
		}
		return $this->prepareDocument($doc, $needRev);
	}



	/**
	 * @return BulkDocument
	 */
	public function getBulkDocument()
	{
		return new BulkDocument('_all_docs', $this);
	}



	/**
	 * @return TempViewQuery
	 */
	public function queryTempView()
	{
		return new TempViewQuery('_temp_view', $this);
	}



	/********************* attachments *********************/



	/**
	 * Create document's attachment from string.
	 * @param  mixed	document or it's id
	 * @param  string
	 * @param  string
	 * @return \stdClass
	 */
	public function attachFileContent($doc, $content, $name)
	{
		$doc = $this->prepareStub($doc);
		$request = $this->createRequest('PUT', rawurlencode($doc->_id) . '/' . rawurlencode($name), array('rev' => $doc->_rev));
		$request->setPostData($content);
		$request->setContentType(finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $content));
		return $this->executeRequest($request);
	}



	/**
	 * Create document's atachment from file.
	 * @param  mixed	document or it's id
	 * @param  string	path to file
	 * @param  string
	 * @return \stdClass
	 */
	public function attachFile($doc, $file, $name = NULL)
	{
		$doc = $this->prepareStub($doc);
		$name = $name === NULL ? basename($file) : $name;
		$request = $this->createRequest('PUT', rawurlencode($doc->_id) . '/' . rawurlencode($name), array('rev' => $doc->_rev));
		$request->setFile($file);
		$request->setContentType(finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file));
		return $this->executeRequest($request);
	}



	/**
	 * Delete attachment.
	 * @param  mixed	document or it's id
	 * @param string
	 * @return \stdClass
	 */
	public function deleteAttachment($doc, $name)
	{
		$doc = $this->prepareStub($doc);
		return $this->makeRequest('DELETE', rawurlencode($doc->_id) . '/' . rawurlencode($name), array('rev' => $doc->_rev));
	}



	/**
	 * Load attachment's content.
	 * @param  mixed	document or it's id
	 * @param  string
	 * @return string
	 */
	public function loadAttachmentContent($doc, $name)
	{
		$doc = $this->prepareStub($doc, FALSE);
		$query = isset($doc->_rev) ? array('rev' => $doc->_rev) : NULL;
		$request = $this->createRequest('GET', rawurlencode($doc->_id) . '/' . rawurlencode($name), $query);
		$request->removeHeader('Accept');
		return $this->executeRequest($request);
	}



	/********************* design *********************/



	/**
	 * Get document's design.
	 * @param string
	 * @return Design
	 */
	public function getDesign($name)
	{
		return new Design($name, $this);
	}



	/********************* request *********************/



	public function createRequest($method, $path = '', array $query = NULL)
	{
		return $this->connection->createRequest($method, $this->name . '/' . $path, $query);
	}



	public function executeRequest(Request $request)
	{
		$response = $this->connection->executeRequest($request);
		if (isset($response->rev)) {
			$this->revs[$response->id] = $response->rev;
		} elseif (isset($response->_rev)) {
			$this->revs[$response->_id] = $response->_rev;
		} elseif (isset($response->rows[0]->doc->_id)) {
			foreach ($response->rows as $v) {
				$this->revs[$v->doc->_id] = $v->doc->_rev;
			}
		}
		return $response;
	}



	public function makeRequest($method, $path = NULL, $query = NULL, $postData = NULL)
	{
		$request = $this->createRequest($method, $path, $query);
		$request->setPostData($postData);
		return $this->executeRequest($request);
	}
}