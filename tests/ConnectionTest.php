<?php

namespace CouchPhp\Tests;

use PHPUnit_Framework_TestCase as TestCase,
	CouchPhp\Connection,
	CouchPhp\Request,
	CouchPhp\RequestException;



class ConnectionTest extends TestCase
{
	private $uri = 'http://example.com';

	private $client;

	private $connection;



	protected function setUp()
	{
		$this->client = new MockClient;
		$this->connection = new Connection('http://example.com', $this->client);
	}



	/**
	 * @test
	 */
	public function createDatabaseMakesPutRequest()
	{
		$this->connection->createDatabase('newdatabase');
		$request = $this->client->getLastRequest();
		$this->assertSame($this->uri . '/newdatabase', $request->getUri());
		$this->assertSame('PUT', $request->getMethod());
	}



	/**
	 * @test
	 */
	public function createDatabaseReturnsItsInstance()
	{
		$this->assertInstanceOf('CouchPhp\Database', $this->connection->createDatabase('newdatabase'));
	}



	/**
	 * @test
	 */
	public function deleteDatabaseMakesDeleteRequest()
	{
		$this->connection->deleteDatabase('newdatabase');
		$request = $this->client->getLastRequest();
		$this->assertSame($this->uri . '/newdatabase', $request->getUri());
		$this->assertSame('DELETE', $request->getMethod());
	}



	/**
	 * @test
	 */
	public function executeRequestReturnsNullForGetRequestAndResponseCode404()
	{
		$this->client->code = 404;
		$request = new Request;
		$request->method = 'GET';
		$this->assertNull($this->connection->executeRequest($request));
	}



	/**
	 * @test
	 */
	public function executeRequestUsesReturnedJsonForThrowedException()
	{
		$this->client->code = 500;
		$this->client->result = array('error' => 'foo', 'reason' => 'bar');
		try {
			$this->connection->executeRequest(new Request);
			$this->fail();
		} catch (RequestException $e) {
			$this->assertSame('foo: bar', $e->getMessage());
		}
	}



	/**
	 * @test
	 */
	public function executeRequestThrowsExceptionForUnknownResponse()
	{
		$this->client->code = 500;
		$this->client->contentType = 'text/html';
		try {
			$this->connection->executeRequest(new Request);
			$this->fail();
		} catch (RequestException $e) {
		}
	}



	/**
	 * @test
	 */
	public function executeRequestThrowsExceptionForNonJsonResponse()
	{
		$this->client->code = 200;
		$this->client->contentType = 'text/html';
		try {
			$request = new Request;
			$request->setHeader('Accept', 'application/json');
			$this->connection->executeRequest($request);
			$this->fail();
		} catch (RequestException $e) {
		}
	}
}