<?php

namespace CouchPhp\Tests;

use PHPUnit_Framework_TestCase as TestCase,
	CouchPhp\Connection;



class ViewQueryTest extends TestCase
{
	private $client;

	private $viewQuery;

	private $uri = 'http://example.com/test/_design/design/_view/view';



	protected function setUp()
	{
		parent::setUp();
		$this->client = new MockClient;
		$this->client->result = (object) array('rows' => array());
		$connection = new Connection('http://example.com', $this->client);
		$this->viewQuery = $connection->getDatabase('test')->getDesign('design')
			->queryView('view');
	}



	/**
	 * @test
	 */
	public function cleanQueryHasNoParams()
	{
		$this->viewQuery->query();
		$this->assertSame($this->uri, $this->client->getLastRequest()->getUri());
	}



	/**
	 * @test
	 */
	public function keysAreInPost()
	{
		$keys = array(1, 2);
		$this->viewQuery->keys($keys);
		$this->viewQuery->query();
		$this->assertSame($this->uri, $this->client->getLastRequest()->getUri());
		$this->assertSame(json_encode(array('keys' => $keys)), $this->client->getLastRequest()->getPostData());
	}



	/**
	 * @test
	 */
	public function startKeyAcceptsVariableNumberOfParams()
	{
		$this->viewQuery->startKey('foo', 'bar');
		$this->viewQuery->query();
		$this->assertSame($this->uri . '?startkey=' . rawurlencode(json_encode(array('foo', 'bar'))), $this->client->getLastRequest()->getUri());
	}



	/**
	 * @test
	 */
	public function endKeyAcceptsVariableNumberOfParams()
	{
		$this->viewQuery->endKey('foo', 'bar');
		$this->viewQuery->query();
		$this->assertSame($this->uri . '?endkey=' . rawurlencode(json_encode(array('foo', 'bar'))), $this->client->getLastRequest()->getUri());
	}



	/**
	 * @test
	 */
	public function reduceSetReduceAndGroupParams()
	{
		$this->viewQuery->reduce(TRUE);
		$this->viewQuery->query();
		$this->assertSame($this->uri . '?reduce=true&group=true', $this->client->getLastRequest()->getUri());
	}



	/**
	 * @test
	 */
	public function reduceSetGroupLevelParam()
	{
		$this->viewQuery->reduce(1);
		$this->viewQuery->query();
		$this->assertSame($this->uri . '?reduce=true&group_level=1', $this->client->getLastRequest()->getUri());
	}



	/**
	 * @test
	 */
	public function queryReturnsQueryResult()
	{
		$this->assertInstanceOf('CouchPhp\QueryResult', $this->viewQuery->query());
	}



	/**
	 * @test
	 */
	public function queryThrowsExceptionForUndefinedView()
	{
		$this->client->result = NULL;
		try {
			$this->viewQuery->query();
			$this->fail();
		} catch (\Exception $e) {
		}
	}
}