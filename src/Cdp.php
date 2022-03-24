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

    /**
     * Open a new tab.
     */
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

        // Fixme: error handling
        $tabId = $body['id']; 
        $debuggerUrl = $body['webSocketDebuggerUrl'];

        return new Tab(
            $tabId,
            $debuggerUrl,
            $this->host,
            $this->port,
            $this->httpClient,
        );
    }

    /**
     * Brings a page into the foreground
     */
    public function activate(Tab $tab) {
        $response = $this->httpClient->request(
            'GET', "http://{$this->host}:{$this->port}/json/activate/{$tab->id}");
        // Fixme: error handling
    }
}
