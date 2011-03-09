<?php

namespace CouchPhp\Tests;

use PHPUnit_Framework_TestCase as TestCase,
	CouchPhp\Connection;



class BulkDocumentTest extends TestCase
{
	private $client;

	private $bulkDocument;

	private $uri = 'http://example.com/test';



	protected function setUp()
	{
		parent::setUp();
		$this->client = new MockClient;
		$connection = new Connection('http://example.com', $this->client);
		$this->bulkDocument = $connection->getDatabase('test')->getBulkDocument();
	}



	/**
	 * @test
	 */
	public function saveMakesPostRequestWithGivenDocs()
	{
		$docs = array(array('foo' => 'bar'));
		$this->bulkDocument->save($docs);
		$request = $this->client->getLastRequest();
		$this->assertSame($this->uri . '/_bulk_docs', $request->getUri());
		$this->assertSame('POST', $request->getMethod());
		$this->assertSame(json_encode(array('all_or_nothing' => FALSE, 'docs' => $docs)), $request->getPostData());
	}



	/**
	 * @test
	 */
	public function saveAcceptsArrayOfArrays()
	{
		$docs = array(array('foo' => 'bar'));
		$this->bulkDocument->save($docs);
		$this->assertSame(json_encode(array('all_or_nothing' => FALSE, 'docs' => $docs)), $this->client->getLastRequest()->getPostData());
	}



	/**
	 * @test
	 */
	public function saveAcceptsArrayOfObjects()
	{
		$docs = array((object) array('foo' => 'bar'));
		$this->bulkDocument->save($docs);
		$this->assertSame(json_encode(array('all_or_nothing' => FALSE, 'docs' => $docs)), $this->client->getLastRequest()->getPostData());
	}



	/**
	 * @test
	 */
	public function saveUsesTraversable()
	{
		$doc = array('foo' => 'bar');
		$docs = array(new FakeDoc($doc));
		$this->bulkDocument->save($docs);
		$this->assertSame(json_encode(array('all_or_nothing' => FALSE, 'docs' => array($doc))), $this->client->getLastRequest()->getPostData());
	}



	/**
	 * @test
	 */
	public function saveAcceptsOnlyArrayOfObjectsOrArrays()
	{
		foreach (array(NULL, 1, M_PI, 'foo', fopen(__FILE__, 'r')) as $doc) {
			try {
				$this->bulkDocument->save(array($doc));
				$this->fail();
			} catch (\InvalidArgumentException $e) { }
		}
	}
}