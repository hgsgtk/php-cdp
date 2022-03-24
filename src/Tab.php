<?php

declare(strict_types=1);

namespace PhpCdp;

use \GuzzleHttp\ClientInterface;

final class Tab
{
    public function __construct(
        public string $id,
        private string $host,
        private string $port,
        private ClientInterface $httpClient,
    )
    {
    }

    public function close() 
    {
        $response = $this->httpClient->request(
            'GET', "http://{$this->host}:{$this->port}/json/close/{$this->id}");
        // Fixme: error handling
    }
}
