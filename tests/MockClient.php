<?php

namespace CouchPhp\Tests;

use CouchPhp\IClient,
	CouchPhp\Request,
	CouchPhp\Response;



class MockClient implements IClient
{
	private $lastRequest;

	private $lastResponse;

	public $code = 200;

	public $contentType = 'application/json';

	public $result;



	/**
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return stdClass|NULL
	 */
	public function makeRequest(Request $request)
	{
		$this->lastRequest = $request;
		$time = microtime();
		return $this->lastResponse = new Response(
			$this->code,
			"Content-Type: $this->contentType",
			json_encode($this->result),
			microtime() - $time
		);
	}



	public function getLastRequest()
	{
		return $this->lastRequest;
	}



	public function getLastResponse()
	{
		return $this->lastResponse;
	}
}