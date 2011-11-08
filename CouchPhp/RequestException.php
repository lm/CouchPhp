<?php

namespace CouchPhp;

use RuntimeException;



require_once __DIR__ . '/nette.php'; // required by apigen



/**
 * Occurs when request cannot be properly fullfilled.
 */
class RequestException extends RuntimeException
{
	private $request;

	private $response;



	public function __construct($message = NULL, $code = 0, Request $request, Response $response = NULL)
	{
		parent::__construct($message, $code);
		$this->request = $request;
		$this->response = $response;
	}




	public function getRequest()
	{
		return $this->request;
	}



	public function getResponse()
	{
		return $this->response;
	}
}
