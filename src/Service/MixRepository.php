<?php

namespace App\Service;

use Psr\Cache\CacheItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MixRepository
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private CacheInterface $cache
    )
    {
    }

    public function findAll(): array
    {
        //checks to see if we have mixes_data in teh cache. if we do it will return that righ away
        //if not, it will make the http request and set mixes_data to the response array
        return $this->cache->get('mixes_data', function(CacheItemInterface $cacheItem){
            $cacheItem->expiresAfter(5);
            $response = $this->httpClient->request('GET', 'https://raw.githubusercontent.com/SymfonyCasts/vinyl-mixes/main/mixes.json');
            return $response->toArray();
        });
    }
}