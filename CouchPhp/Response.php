<?php

namespace CouchPhp;



/**
 * Server response.
 *
 * @property-read int $code
 * @property-read array $headers
 * @property-read string $body
 * @property-read mixed $json
 * @property-read int $time
 */
class Response extends Object
{
	/** @var int */
	private $code;

	/** @var string */
	private $headers;

	/** @var string */
	private $body;

	/** @var mixed */
	private $json;

	/** @var int */
	private $time;



	/**
	 * @param int
	 * @param string
	 * @param string
	 * @param int
	 */
	public function __construct($code, $headers, $body, $time)
	{
		$this->code = (int) $code;
		$this->headers = $headers;
		$this->body = $body;
		$this->time = $time;
	}



	/**
	 * @return int
	 */
	public function getCode()
	{
		return $this->code;
	}



	/**
	 * @param  string
	 * @return string|NULL
	 */
	public function getHeader($name)
	{
		return preg_match('#^' . preg_quote($name, '#') . ': *(.*?)\r?$#mi', $this->headers, $m) ? $m[1] : NULL;
	}



	/**
	 * @return array
	 */
	public function getHeaders()
	{
		preg_match_all('#^([^:\n]+): *(.*?)\r?$#m', $this->headers, $m);
		return array_combine($m[1], $m[2]);
	}



	/**
	 * @return string
	 */
	public function getBody()
	{
		return $this->body;
	}



	/**
	 * Returns true if response body contains JSON.
	 * @return bool
	 */
	public function isJson()
	{
		$contentType = $this->getHeader('Content-Type');
		return $contentType != NULL && (substr_compare($contentType, 'application/json', 0, 16) === 0
			|| (substr_compare($contentType, 'text/plain', 0, 10) === 0 && $this->body[0] === '{'));
	}



	/**
	 * Returns decoded JSON body.
	 * @return mixed
	 */
	public function getJson()
	{
		if ($this->json === NULL) {
			$this->json = Json::decode($this->body);
		}
		return $this->json;
	}



	/**
	 * @return int
	 */
	public function getTime()
	{
		return $this->time;
	}
}
