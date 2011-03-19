<?php

namespace CouchPhp\Clients;

use CouchPhp\Object,
	CouchPhp\IClient,
	CouchPhp\Request,
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
	 * @return Response
	 */
	public function makeRequest(Request $request)
	{
		if (!$request->isCacheable()) {
			return $this->client->makeRequest($request);
		}

		$cResponse = $this->cache[$request];
		if ($cResponse !== NULL) {
			$cRequest = clone $request;
			$cRequest->setHeader('If-None-Match', $cResponse->getHeader('Etag'));
			$response = $this->client->makeRequest($cRequest);
			if ($response->getCode() === 304) { // Not modified
				return $cResponse;
			}
		} else {
			$response = $this->client->makeRequest($request);
		}

		if ($response->isJson()) {
			$this->cache->save($request, $response);
		}
		return $response;
	}
}