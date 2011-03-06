<?php

namespace CouchPhp;



/**
 * Query builder for temporaly view.
 */
class TempViewQuery extends BaseBulkDocument
{
	/**
	 * @param  string
	 * @return TempViewQuery
	 */
	public function mapFunction($function)
	{
		$this->postData['map'] = $function;
		return $this;
	}



	/**
	 * @param  string
	 * @return TempViewQuery
	 */
	public function reduceFunction($function)
	{
		$this->postData['reduce'] = $function;
		return $this;
	}
}