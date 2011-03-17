<?php

namespace CouchPhp;



/**
 * Server response.
 */
class Response extends Object
{
	private $code;

	private $headers;

	private $body;

	private $json;

	private $time;



	public function __construct($code, $headers, $body, $time)
	{
		$this->code = (int) $code;
		$this->headers = $headers;
		$this->body = $body;
		$this->time = $time;
	}



	public function getCode()
	{
		return $this->code;
	}



	public function getHeader($name)
	{
		return preg_match('#^' . preg_quote($name, '#') . ': *(.*?)\r?$#mi', $this->headers, $m) ? $m[1] : NULL;
	}



	public function getHeaders()
	{
		preg_match_all('#^([^:]+): *(.*?)\r?$#m', $this->headers, $m);
		return array_combine($m[1], $m[2]);
	}



	public function getBody()
	{
		return $this->body;
	}



	public function isJson()
	{
		$contentType = $this->getHeader('Content-Type');
		return $contentType != NULL && (substr_compare($contentType, 'application/json', 0, 16) === 0
			|| (substr_compare($contentType, 'text/plain', 0, 10) === 0 && $this->body[0] === '{'));
	}



	public function getJson()
	{
		if ($this->json === NULL) {
			$this->json = Json::decode($this->body);
		}
		return $this->json;
	}



	public function getTime()
	{
		return $this->time;
	}
}
