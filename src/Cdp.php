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

    public function open(?string $targetUrl = null): Tab 
    {
        $url = "http://{$this->host}:{$this->port}/json/new";
        if (!is_null($url)) {
            $url .= "?{$targetUrl}";
        }
        $response = $this->httpClient->request('GET', $url);
        $body = json_decode($response->getBody()->getContents(), true);
        if (!$body) {
            // Fixme: error handling
        }

        $tabId = $body['id']; // Fixme: error handling
        return new Tab(
            $tabId,
            $this->host,
            $this->port,
            $this->httpClient,
        );
    }
}
