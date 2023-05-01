<?php

namespace App\Service;

use Psr\Cache\CacheItemInterface;
use Symfony\Bridge\Twig\Command\DebugCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class MixRepository
{
    public function __construct(
        private HttpClientInterface $githubContentClient,
        private CacheInterface $cache,
//        another way to set $isDebug to the kernel.debug parameter
        #[Autowire('%kernel.debug%')]
        private bool $isDebug,
        //since debugcommand isn't autowirable, we use service: 'twig.command.debug' to point to the service
        #[Autowire (service: 'twig.command.debug')]
        private DebugCommand $twigDebugCommand
    )
    {
    }

    public function findAll(): array
    {
        //executing a console log
        /*
        $output = new BufferedOutput();
        $this->twigDebugCommand->run(new ArrayInput([]), $output);
        dd($output);
        */

        //checks to see if we have mixes_data in teh cache. if we do it will return that righ away
        //if not, it will make the http request and set mixes_data to the response array
        return $this->cache->get('mixes_data', function(CacheItemInterface $cacheItem){
            //if we are in debug mode, cache for 5 seconds. else cache for 60
            $cacheItem->expiresAfter($this->isDebug ? 5 : 60);
            $response = $this->githubContentClient->request('GET', '/SymfonyCasts/vinyl-mixes/main/mixes.json');
            return $response->toArray();
        });
    }
}