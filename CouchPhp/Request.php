<?php

namespace CouchPhp;



/**
 * Client request.
 *
 * @property string $method
 * @property string $uri
 * @property-read array $headers
 * @property string $postData
 * @property string $file
 */
class Request extends Object
{
	/** @var string */
	private $method;

	/** @var string */
	private $uri;

	/** @var array */
	private $headers = array();

	/** @var string */
	private $postData;

	/** @var string */
	private $file;



	/**
	 * @param string
	 */
	public function setMethod($method)
	{
		$this->method = $method;
	}



	/**
	 * @param string
	 */
	public function getMethod()
	{
		return $this->method;
	}



	/**
	 * @param string
	 */
	public function setUri($uri)
	{
		$this->uri = $uri;
	}



	/**
	 * @return string
	 */
	public function getUri()
	{
		return $this->uri;
	}



	/**
	 * @param string
	 * @param string
	 */
	public function setHeader($name, $value)
	{
		$this->headers[$name] = $value;
	}



	/**
	 * @param string
	 */
	public function removeHeader($name)
	{
		unset($this->headers[$name]);
	}



	/**
	 * @param string
	 * @return string
	 */
	public function getHeader($name)
	{
		return isset($this->headers[$name]) ? $this->headers[$name] : NULL;
	}



	/**
	 * @return string
	 */
	public function getHeaders()
	{
		return $this->headers;
	}



	/**
	 * @param string
	 */
	public function setContentType($contentType)
	{
		$this->setHeader('Content-Type', $contentType);
	}



	/**
	 * @return string
	 */
	public function getContentType()
	{
		return $this->getHeader('Content-Type');
	}



	/**
	 * @param string
	 */
	public function setPostData($data)
	{
		$this->postData = (string) $data;
	}



	/**
	 * @return string
	 */
	public function getPostData()
	{
		return $this->postData;
	}



	/**
	 * @param string
	 */
	public function setFile($file)
	{
		$this->file = $file;
	}



	/**
	 * @return string
	 */
	public function getFile()
	{
		return $this->file;
	}



	/**
	 * @return bool
	 */
	public function isCacheable()
	{
		if ($this->method === 'GET') {
			return TRUE;
		}
		$path = parse_url($this->getUri(), PHP_URL_PATH);
		if ($path == '') {
			return FALSE;
		}
		$path = substr($path, strpos($path, '/', 1) + 1);
		return $path === '_all_docs' || preg_match('#_design/[^/]/_(view|list|show)/.#A', $path);
	}
}