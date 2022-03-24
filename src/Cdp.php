<?php

declare(strict_types=1);

namespace PhpCdp;

use GuzzleHttp\Client;
use \GuzzleHttp\ClientInterface;

final class Cdp
{
    private ClientInterface $httpClient;

    public function __construct(
        private string $host,
        private string $port,
        ?ClientInterface $httpClient = null,
    )
    {
        $this->httpClient = $httpClient ?? new Client();
    }

    public function tab(): Tab {
        $response = $this->httpClient->request('POST', "http://{$this->host}:{$this->port}/json/new");
        return new Tab();
    }
}
