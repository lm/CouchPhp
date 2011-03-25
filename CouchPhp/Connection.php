<?php

namespace CouchPhp;



require_once __DIR__ . '/nette.php';
require_once __DIR__ . '/IClient.php';
require_once __DIR__ . '/Profiling/IProfiler.php';
require_once __DIR__ . '/Object.php';
require_once __DIR__ . '/Json.php';
require_once __DIR__ . '/Request.php';
require_once __DIR__ . '/Response.php';
require_once __DIR__ . '/Profiling/DebugPanel.php';
require_once __DIR__ . '/RequestException.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Design.php';
require_once __DIR__ . '/QueryResult.php';
require_once __DIR__ . '/BaseBulkDocument.php';
require_once __DIR__ . '/ViewQuery.php';
require_once __DIR__ . '/TempViewQuery.php';
require_once __DIR__ . '/BulkDocument.php';
require_once __DIR__ . '/LuceneQuery.php';
require_once __DIR__ . '/Clients/CurlClient.php';
require_once __DIR__ . '/Clients/CacheClient.php';



/**
 * Represents connection to CouchDB.
 *
 * @property-read string $uri
 * @property-read IClient $client
 * @property IProfiler $profiler
 * @property-read string $version
 */
class Connection extends Object
{
	/** @var string */
	private $uri;

	/** @var IClient */
	private $client;

	/** @var IProfiler */
	private $profiler;



	/**
	 * @param string
	 * @param IClient|NULL
	 */
	public function __construct($uri = '127.0.0.1:5984', IClient $client = NULL)
	{
		$this->uri = $uri;
		$this->client = $client ?: new Clients\CurlClient;
	}



	/**
	 * @return string
	 */
	public function getUri()
	{
		return $this->uri;
	}



	/**
	 * @return IClient
	 */
	public function getClient()
	{
		return $this->client;
	}



	/**
	 * @param IProfiler
	 */
	public function setProfiler(IProfiler $profiler)
	{
		$this->profiler = $profiler;
	}



	/**
	 * @return IProfiler|NULL
	 */
	public function getProfiler()
	{
		return $this->profiler;
	}



	/**
	 * Register panel to Nette\Debug.
	 */
	public function registerDebugPanel()
	{
		$this->profiler = new DebugPanel($this);
		return $this;
	}



	/**
	 * Get CouchDB version.
	 * @return string
	 */
	public function getVersion()
	{
		return $this->makeRequest('GET')->version;
	}



	/**
	 * Get list of unique ids, for usage as documents ids.
	 * @param  int
	 * @return array
	 */
	public function getUniqueIds($count = 1)
	{
		return $this->makeRequest('GET', '_uuids', array('count' => (int) $count))->uuids;
	}



	/********************* Database *********************/



	/**
	 * Get database.
	 * @param  string	database name
	 * @return Database
	 */
	public function getDatabase($name)
	{
		return new Database($this, $name);
	}



	/**
	 * @return array	database names
	 */
	public function getDatabaseNames()
	{
		return $this->makeRequest('GET', '_all_dbs');
	}



	/**
	 * Create new database and returns it's instance.
	 * @param  string	database name
	 * @return Database
	 */
	public function createDatabase($name)
	{
		$this->makeRequest('PUT', $name);
		return $this->getDatabase($name);
	}



	/**
	 * Delete database.
	 * @param string
	 */
	public function deleteDatabase($name)
	{
		$this->makeRequest('DELETE', $name);
	}



	/********************* Request *********************/



	/**
	 * Create request.
	 * @param  string
	 * @param  string
	 * @param  array
	 * @return Request
	 */
	public function createRequest($method, $path = '', array $query = NULL)
	{
		$request = new Request;
		$request->setMethod($method);
		$request->setUri($this->uri . '/' . $path . ($query ? '?' . http_build_query($query, '', '&') : ''));
		$request->setContentType('application/json');
		$request->setHeader('Accept', 'application/json');
		return $request;
	}



	/**
	 * Execute request.
	 * @param  Request
	 * @return stdClass|string
	 * @throws RequestException
	 */
	public function executeRequest(Request $request)
	{
		$response = $this->client->makeRequest($request, $this->profiler);
		$data = ($isJson = $response->isJson()) ? $response->getJson() : $response->getBody();

		if ($request->getHeader('Accept') === 'application/json' && !$isJson) {
			$cType = $response->getHeader('Content-Type');
			throw new RequestException("Server returns incorrect 'Content-Type: $cType', 'application/json' was expected", 0, $request, $response);

		} elseif ($response->code < 400) {
			return $data;

		} elseif ($response->code === 404 && $request->method === 'GET') {
			return NULL;

		} elseif (isset($data->reason)) {
			$msg = (isset($data->error) ? "$data->error: " : '') . "$data->reason";
			throw new RequestException($msg, $response->code, $request, $response);

		} else {
			throw new RequestException('CouchDB client returned unknown response', 0, $request, $response);
		}
	}



	/**
	 * Shortcut for  executeRequest(createRequest(...))
	 * @param  string
	 * @param  string
	 * @param  array
	 * @param  string
	 * @return stdClass|string
	 */
	public function makeRequest($method, $path = '', $query = NULL, $postData = NULL)
	{
		$request = $this->createRequest($method, $path, $query);
		$request->setPostData($postData);
		return $this->executeRequest($request);
	}
}