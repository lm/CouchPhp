<?php

namespace CouchPhp\Clients;

use CouchPhp\Object,
	CouchPhp\IClient,
	CouchPhp\Request,
	CouchPhp\IProfiler,
	Nette\Caching\Cache;



/**
 * Wraps regular client and caches it's requests.
 */
class CacheClient extends Object implements IClient
{
	/** @var IClient */
	private $client;

	/** @var Cache */
	private $cache;



	/**
	 * @param IClient
	 * @param Cache
	 */
	public function __construct(IClient $client, Cache $cache)
	{
		$this->client = $client;
		$this->cache = $cache;
	}




	/**
	 * @param  Request
	 * @param  IProfiler
	 * @return Response
	 */
	public function makeRequest(Request $request, IProfiler $profiler = NULL)
	{
		if (!$request->isCacheable()) {
			return $this->client->makeRequest($request, $profiler);
		}

		$cResponse = $this->cache[$request];
		if ($cResponse !== NULL) {
			$cRequest = clone $request;
			$cRequest->setHeader('If-None-Match', $cResponse->getHeader('Etag'));
			$response = $this->client->makeRequest($cRequest, $profiler);
			if ($response->getCode() === 304) { // Not modified
				return $cResponse;
			}
		} else {
			$response = $this->client->makeRequest($request, $profiler);
		}

		if ($response->isJson()) {
			$this->cache->save($request, $response);
		}
		return $response;
	}
}