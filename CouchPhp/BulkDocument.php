<?php

namespace CouchPhp;



/**
 * Bulk document query builder.
 */
class BulkDocument extends BaseBulkDocument
{
	/**
	 * Save documents.
	 * @param  array	list of documents
	 * @param  bool
	 * @return stdClass
	 */
	public function save(array $documents, $allOrNothing = FALSE)
	{
		$docs = array();
		foreach ($documents as $i => $doc) {
			$docs[] = $this->database->prepareDocument($doc, FALSE);
		}
		return $this->database->makeRequest(
			'POST',
			'_bulk_docs',
			NULL,
			Json::encode(array('all_or_nothing' => $allOrNothing, 'docs' => $docs))
		);
	}
}