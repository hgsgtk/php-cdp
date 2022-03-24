<?php

declare(strict_types=1);

namespace PhpCdp;

use \WebSocket\Client;

final class DevToolsClient
{
    private Client $client;

    /**
     * @var string $debuggerUrl a WebSocket URL of DevTools debugging protocol.
     *             e.g. ws://localhost:9222/devtools/page/DAB7FB6187B554E10B0BD18821265734
     */
    public function __construct(string $debuggerUrl)
    {
        $this->client = new Client($debuggerUrl);
    }

    public function ping()
    {
        $this->client->ping();
    }
}
