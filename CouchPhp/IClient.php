<?php

namespace CouchPhp;



/**
 * CouchDB client.
 */
interface IClient
{
	/**
	 * @param  Request
	 * @return Response
	 * @throws RequestException
	 */
	function makeRequest(Request $request);
}