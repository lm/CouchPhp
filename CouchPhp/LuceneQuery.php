<?php

namespace CouchPhp;



/**
 * Lucene helper.
 */
class LuceneQuery
{
	public static function escape($s)
	{
		$s = addcslashes($s, '+-&|!(){}[]^"~*?:\\');
		$s = preg_replace('#NOT|AND|OR#', '\\\\$0', $s);
		return $s;
	}
}