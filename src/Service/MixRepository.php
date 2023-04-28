<?php

namespace App\Service;

use Psr\Cache\CacheItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class MixRepository
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private CacheInterface $cache,
//        another way to set $isDebug to the kernel.debug parameter
        #[Autowire('%kernel.debug%')]
        private bool $isDebug
    )
    {
    }

    public function findAll(): array
    {
        //checks to see if we have mixes_data in teh cache. if we do it will return that righ away
        //if not, it will make the http request and set mixes_data to the response array
        return $this->cache->get('mixes_data', function(CacheItemInterface $cacheItem){
            //if we are in debug mode, cache for 5 seconds. else cache for 60
            $cacheItem->expiresAfter($this->isDebug ? 5 : 60);
            $response = $this->httpClient->request('GET', 'https://raw.githubusercontent.com/SymfonyCasts/vinyl-mixes/main/mixes.json');
            return $response->toArray();
        });
    }
}