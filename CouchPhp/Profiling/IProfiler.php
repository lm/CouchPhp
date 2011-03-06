<?php

namespace CouchPhp;



/**
 * Connection profiler.
 */
interface IProfiler
{
	function logRequest(Request $request);

	function logResponse(Response $response);
}