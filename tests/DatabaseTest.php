<?php

namespace CouchPhp\Tests;

use PHPUnit_Framework_TestCase as TestCase,
	CouchPhp\Connection,
	CouchPhp\Request,
	InvalidArgumentException;



class DatabaseTest extends TestCase
{
	private $uri = 'http://example.com';

	private $client;

	private $database;



	protected function setUp()
	{
		$this->client = new MockClient;
		$connection = new Connection('http://example.com', $this->client);
		$this->database = $connection->getDatabase('test');
	}



	/**
	 * @test
	 */
	public function loadMakesGetRequest()
	{
		$this->database->load('id');
		$request = $this->client->getLastRequest();
		$this->assertSame($this->uri . '/test/id', $request->getUri());
		$this->assertSame('GET', $request->getMethod());
	}



	/**
	 * @test
	 */
	public function loadEncodesId()
	{
		$id = '<>"#%{}/;?:@=';
		$this->database->load($id);
		$request = $this->client->getLastRequest();
		$this->assertSame($this->uri . '/test/' . rawurlencode($id), $request->getUri());
	}



	/**
	 * @test
	 */
	public function saveMakesPostRequest()
	{
		$doc = array('foo' => 'bar');
		$this->database->save($doc);
		$request = $this->client->getLastRequest();
		$this->assertSame($this->uri . '/test/', $request->getUri());
		$this->assertSame('POST', $request->getMethod());
		$this->assertSame(json_encode($doc), $request->getPostData());
	}



	/**
	 * @test
	 */
	public function saveMakesPutRequestWhenIdIsPresent()
	{
		$doc = array('_id' => 'id');
		$this->database->save($doc);
		$request = $this->client->getLastRequest();
		$this->assertSame($this->uri . '/test/id', $request->getUri());
		$this->assertSame('PUT', $request->getMethod());
		$this->assertSame(json_encode($doc), $request->getPostData());
	}



	/**
	 * @test
	 */
	public function saveEncodesId()
	{
		$doc = array('_id' => '<>"#%{}/;?:@=');
		$this->database->save($doc);
		$request = $this->client->getLastRequest();
		$this->assertSame($this->uri . '/test/' . rawurlencode($doc['_id']), $request->getUri());
	}



	/**
	 * @test
	 */
	public function saveAcceptsArray()
	{
		$doc = array('foo' => 'bar');
		$this->database->save($doc);
		$this->assertSame(json_encode($doc), $this->client->getLastRequest()->getPostData());
	}



	/**
	 * @test
	 */
	public function saveAcceptsObject()
	{
		$doc = (object) array('foo' => 'bar');
		$this->database->save($doc);
		$this->assertSame(json_encode($doc), $this->client->getLastRequest()->getPostData());
	}



	/**
	 * @test
	 */
	public function saveUsesTraversable()
	{
		$doc = array('foo' => 'bar');
		$this->database->save(new FakeDoc($doc));
		$this->assertSame(json_encode($doc), $this->client->getLastRequest()->getPostData());
	}



	/**
	 * @test
	 */
	public function saveAccepstOnlyArrayOrObject()
	{
		foreach (array(NULL, 1, M_PI, 'foo', fopen(__FILE__, 'r')) as $doc) {
			try {
				$this->database->save($doc);
				$this->fail();
			} catch (\InvalidArgumentException $e) { }
		}
	}



	/**
	 * @test
	 */
	public function deleteMakesDeleteRequest()
	{
		$this->database->delete('id', 'rev');
		$request = $this->client->getLastRequest();
		$this->assertSame($this->uri . '/test/id?rev=rev', $request->getUri());
		$this->assertSame('DELETE', $request->getMethod());
	}



	/**
	 * @test
	 */
	public function attachFileContentMakesPutRequest()
	{
		$this->database->attachFileContent(array('_id' => 'id', '_rev' => 'rev'), '<?xml version="1.0" ?><el />', 'test.xml');
		$request = $this->client->getLastRequest();
		$this->assertSame($this->uri . '/test/id/test.xml?rev=rev', $request->getUri());
		$this->assertSame('PUT', $request->getMethod());
	}



	/**
	 * @test
	 */
	public function attachFileContentUsesPostDataRequestProperty()
	{
		$this->database->attachFileContent(array('_id' => 'id', '_rev' => 'rev'), '<?xml version="1.0" ?><el />', 'test.xml');
		$request = $this->client->getLastRequest();
		$this->assertSame('<?xml version="1.0" ?><el />', $request->getPostData());
	}



	/**
	 * @test
	 */
	public function attachFileContentDetectsMimeType()
	{
		$this->database->attachFileContent(array('_id' => 'id', '_rev' => 'rev'), '<?xml version="1.0" ?><el />', 'test.xml');
		$request = $this->client->getLastRequest();
		$this->assertSame('application/xml', $request->getContentType());
	}



	/**
	 * @test
	 */
	public function attachFileMakesPutRequest()
	{
		$this->database->attachFile(array('_id' => 'id', '_rev' => 'rev'), __DIR__ . '/phpunit.xml', 'test.xml');
		$request = $this->client->getLastRequest();
		$this->assertSame($this->uri . '/test/id/test.xml?rev=rev', $request->getUri());
		$this->assertSame('PUT', $request->getMethod());
	}



	/**
	 * @test
	 */
	public function attachFileUsesFileRequestProperty()
	{
		$this->database->attachFile(array('_id' => 'id', '_rev' => 'rev'), __DIR__ . '/phpunit.xml');
		$request = $this->client->getLastRequest();
		$this->assertSame(__DIR__ . '/phpunit.xml', $request->getFile());
	}



	/**
	 * @test
	 */
	public function attachFileDetectsMimeType()
	{
		$this->database->attachFile(array('_id' => 'id', '_rev' => 'rev'), __DIR__ . '/phpunit.xml', 'test.xml');
		$request = $this->client->getLastRequest();
		$this->assertSame('application/xml', $request->getContentType());
	}



	/**
	 * @test
	 */
	public function attachFileUsesRealFileName()
	{
		$this->database->attachFile(array('_id' => 'id', '_rev' => 'rev'), __DIR__ . '/phpunit.xml');
		$request = $this->client->getLastRequest();
		$this->assertSame($this->uri . '/test/id/phpunit.xml?rev=rev', $request->getUri());
	}



	/**
	 * @test
	 */
	public function deleteAttachmentMakesDeleteRequest()
	{
		$this->database->deleteAttachment(array('_id' => 'id', '_rev' => 'rev'), 'test.xml');
		$request = $this->client->getLastRequest();
		$this->assertSame($this->uri . '/test/id/test.xml?rev=rev', $request->getUri());
		$this->assertSame('DELETE', $request->getMethod());
	}



	/**
	 * @test
	 */
	public function loadAttachmentContentMakesGetRequest()
	{
		$this->database->loadAttachmentContent(array('_id' => 'id', '_rev' => 'rev'), 'test.xml');
		$request = $this->client->getLastRequest();
		$this->assertSame($this->uri . '/test/id/test.xml?rev=rev', $request->getUri());
		$this->assertSame('GET', $request->getMethod());
		$this->assertNull($request->getHeader('Accept'));
	}



	/**
	 * @test
	 */
	public function prepareDocumentUsesRevisionFromExecutedRequests()
	{
		$request = new Request;
		$this->client->result = array('id' => 1, 'rev' => 1);
		$this->database->executeRequest($request);
		$this->assertSame(1, $this->database->prepareDocument(array('_id' => 1))->_rev);

		$this->client->result = array('_id' => 2, '_rev' => 2);
		$this->database->executeRequest($request);
		$this->assertSame(2, $this->database->prepareDocument(array('_id' => 2))->_rev);

		$this->client->result = array('rows' => array(array('doc' => array('_id' => 3, '_rev' => 3))));
		$this->database->executeRequest($request);
		$this->assertSame(3, $this->database->prepareDocument(array('_id' => 3))->_rev);
	}



	/**
	 * @test
	 */
	public function prepareDocumentThrowsExceptionForUnknownRevision()
	{
		try {
			$this->database->prepareDocument(array('_id' => 1));
			$this->fail();
		} catch (InvalidArgumentException $e) {
		}
	}
}