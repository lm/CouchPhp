<?php

require_once __DIR__ . '/../CouchPhp/Connection.php';



// create connection to 127.0.0.1:5984
$connection = new CouchPhp\Connection;

// create new database
$database = $connection->createDatabase('couchphp_demo');

// store document
$doc = (object) array('foo' => 'bar');
$id = $database->save($doc)->id;

// load document
$doc = $database->load($id);

// update document
$doc->bar = 'foo';
$database->save($doc);

// delete document
$database->delete($doc->_id);

// delete database
$connection->deleteDatabase($database->name);