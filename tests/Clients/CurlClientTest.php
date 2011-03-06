<?php

namespace CouchPhp\Tests;

use PHPUnit_Framework_TestCase as TestCase,
	CouchPhp\Clients\CurlClient,
	CouchPhp\Request,
	CouchPhp\RequestException;



class CurlClientTest extends TestCase
{
	private $client;

	private $uri;



	protected function setUp()
	{
		parent::setUp();
		$this->client = new CurlClient;
		$this->uri = 'http://couchdb.l/couchphp_was_here';
		$this->makeHttpRequest('PUT');
	}



	protected function tearDown()
	{
		$this->makeHttpRequest('DELETE');
	}



	private function makeHttpRequest($method)
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_TIMEOUT, 2);
		curl_setopt($curl, CURLOPT_URL, $this->uri);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($curl, CURLOPT_POSTFIELDS, '');
		curl_exec($curl);
		if ($code = curl_errno($curl)) {
			throw new \Exception(curl_error($curl), $code);
		}
	}



	private function makeClientRequest($method, $uri, $postData, $file, $contentType = 'application/json')
	{
		$request = new Request;
		$request->setMethod($method);
		$request->setUri($uri);
		$request->setContentType($contentType);
		$request->setPostData($postData);
		$request->setFile($file);
		return $this->client->makeRequest($request);
	}



	/**
	 * @test
	 */
	public function makeRequestThrowsRequestExceptionIfConnectionFail()
	{
		try {
			$this->makeClientRequest('GET', 'http://666.6.6.6', NULL, NULL);
			$this->fail();
		} catch (RequestException $e) {
		}
	}



	/**
	 * @test
	 */
	public function makeRequestReturnsResponse()
	{
		$response = $this->makeClientRequest('POST', $this->uri, '{"foo":"bar"}', NULL);
		$this->assertInstanceOf('CouchPhp\Response', $response);
		$this->assertSame(201, $response->code);
		$this->assertInternalType('string', $response->body);
		$this->assertObjectHasAttribute('time', $response);
	}
}