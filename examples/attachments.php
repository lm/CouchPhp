<?php

require_once __DIR__ . '/../CouchPhp/Connection.php';



$connection = new CouchPhp\Connection;
$database = $connection->createDatabase('couchphp_demo');



// create empty document
$docId = $database->save(array())->id;

// create attachment by file's content
$database->attachFileContent($docId, '<?xml version="1.0" ?><foo></foo>', 'foo.xml');

// create attachment by file's path
$database->attachFile($docId, __FILE__);

// load attachment
$database->loadAttachmentContent($docId, 'foo.xml');

// delete attachment
$database->deleteAttachment($docId, 'foo.xml');



$connection->deleteDatabase($database->name);