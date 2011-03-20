<?php

require_once __DIR__ . '/../CouchPhp/Connection.php';



$connection = new CouchPhp\Connection;
$database = $connection->createDatabase('couchphp_demo');



// prepare some documents
$database->bulkDocument->save(array(
	array('_id' => 'User:John', 'docType' => 'User', 'username' => 'John'),
	array('_id' => 'Role:Administrator', 'docType' => 'Role', 'name' => 'administrator'),
));

// query build-in view (_all_docs)
$docs = $database->bulkDocument->keys(array('User:John', 'Role:Administrator'))->fetchDocs();

// create user view
$database->save(array('_id' => '_design/User', 'language' => 'javascript', 'views' => array(
	'byUsername' => array('map' => 'function (doc) { if (doc.docType === "User") emit(doc.username, doc); }'),
)));

// query user view
$values = $database->getDesign('User')->queryView('byUsername')->desc()->limit(10)->fetchValues();



$connection->deleteDatabase($database->name);