<?php

namespace CouchPhp;



/**
 * CouchDB client.
 */
interface IClient
{
	/**
	 * @param  Request
	 * @param  IProfiler
	 * @return Response
	 * @throws RequestException
	 */
	function makeRequest(Request $request, IProfiler $profiler = NULL);
}