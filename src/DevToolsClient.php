<?php

declare(strict_types=1);

namespace PhpCdp;

use InvalidArgumentException;
use stdClass;
use \WebSocket\Client;

final class DevToolsClient
{
    private Client $client;

    /**  
     * @var [string]callable
     * 
     * In the case of Network.enable() and so on,
     * Many domain notifications are sent from browsers
     * on a WebSocket connection.
     * 
     * The listener functions handles the matched domain notification.
     */
    private array $listeners = [];

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

    /**
     * Send commands
     */
    public function command(array $command): array
    {
        $json = json_encode($command);
        if (!$json) {
            // Fixme: proper exception class
            throw new \RuntimeException("cannot encode JSON");
        }
        
        $this->client->send($json);
        // every command message should have id.
        return $this->receive($command['id']);;
    }

    private function receive(int $id): array {
        while (1) {
            $recv = $this->client->receive();
            $decoded = json_decode($recv, true);
            if (!$decoded) {
                throw new \RuntimeException("cannot decode a received message");
            }

            if (array_key_exists('method', $decoded)) {
                $recvMethod = $decoded['method'];
                foreach ($this->listeners as $method => $listener) {
                    if ($method == $recvMethod) {
                        // Event has the `params` field.
                        $listener($decoded['params']);
                        continue;
                    }
                }    
            }

            if (!array_key_exists('id', $decoded)) {
                // Other notifications
                continue;
            }
            if ($decoded['id'] == $id) {
                return $decoded;
            }
        }
    }

    /**
     * Add a listener to handle a message with a given message.
     * @var string $method defined method
     * @var callable $listener func(array $params) {} 
     */
    public function addListener(string $method, callable $listener) {
        if (array_key_exists($method, $this->listeners)) {
            throw new InvalidArgumentException('not supported multiple listeners for a single method');
        }
        $this->listeners[$method] = $listener;
    }

    public function receiveUntil(int $waitSecond) {
        $start = hrtime(true);

        while ((hrtime(true) - $start) / 1000000000 <= $waitSecond) {
            $recv = $this->client->receive();
            $decoded = json_decode($recv, true);
            if (!$decoded) {
                throw new \RuntimeException("cannot decode a received message");
            }

            if (array_key_exists('method', $decoded)) {
                $recvMethod = $decoded['method'];
                foreach ($this->listeners as $method => $listener) {
                    if ($method == $recvMethod) {
                        // Event has the `params` field.
                        $listener($decoded['params']);
                        continue;
                    }
                }    
            }
        }
    }
}
