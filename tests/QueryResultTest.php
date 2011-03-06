<?php

namespace CouchPhp\Tests;

use PHPUnit_Framework_TestCase as TestCase,
	CouchPhp\QueryResult,
	OutOfBoundsException;



class QueryResultTest extends TestCase
{
	private $rows;



	private function createQueryResult()
	{
		$this->rows = array(
			(object) array('_id' => 1, '_rev' => 1),
			(object) array('_id' => 2, '_rev' => 2),
		);
		return new QueryResult($this->rows);
	}



	/**
	 * @test
	 */
	public function fetchReturnsFirstRow()
	{
		$result = $this->createQueryResult();
		$this->assertEquals(current($this->rows), $result->fetch());
	}



	/**
	 * @test
	 */
	public function fetchReturnsNullForEmptyResult()
	{
		$result = $this->createEmptyQueryResult();
		$this->assertNull($result->fetch());
	}



	/**
	 * @test
	 */
	public function fetchRowsUseNullForUnknownKey()
	{
		$result = new QueryResult(array(
			(object) array('_id' => 1, '_rev' => 1, 'error' => 'true'),
		));
		$this->assertSame(array(NULL), $result->fetchRows());
	}




	/**
	 * @test
	 */
	public function fetchDocsUseNullForUnknownKey()
	{
		$result = new QueryResult(array(
			(object) array('_id' => 1, '_rev' => 1, 'error' => 'true'),
		));
		$this->assertSame(array(NULL), $result->fetchDocs());
	}



	/**
	 * @test
	 */
	public function fetchDocsThrowsExceptionIfRowNotCountainsDoc()
	{
		$result = new QueryResult(array(
			(object) array('_id' => 1, '_rev' => 1),
		));
		try {
			$result->fetchDocs();
			$this->fail();
		} catch (OutOfBoundsException $e) {
		}
	}



	/**
	 * @test
	 */
	public function fetchValuesUseNullForUnknownKey()
	{
		$result = new QueryResult(array(
			(object) array('_id' => 1, '_rev' => 1, 'error' => 'true'),
		));
		$this->assertSame(array(NULL), $result->fetchValues());
	}



	private function createEmptyQueryResult()
	{
		return new QueryResult(array());
	}
}