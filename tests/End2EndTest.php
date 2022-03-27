<?php

declare(strict_types=1);

use PhpCdp\Cdp;
use PhpCdp\DevToolsClient;
use Ramsey\Uuid\Uuid;

final class End2EndTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function testGotoPage(): void
    {
        $cdp = new Cdp('127.0.0.1', '9222');
        try {
            $tab = $cdp->open('https://autify.com');
        } finally {
            $tab->close();
        }
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testSwitchActiveTag(): void
    {
        $cdp = new Cdp('127.0.0.1', '9222');
        try {
            $autifyTab = $cdp->open('https://autify.com');
            $exampleTab = $cdp->open('https://example.com');
            $blankTab = $cdp->open();

            $cdp->activate($autifyTab);
            
        } finally {
            $autifyTab->close();
            $exampleTab->close();
            $blankTab->close();
        }
    }

    public function testDevTools_setDiscoverTargets(): void
    {
        try {
            $cdp = new Cdp('127.0.0.1', '9222');
            $tab = $cdp->open('https://autify.com');
    
            $devTools = new DevToolsClient($tab->debuggerUrl);
            $devTools->ping();
    
            // https://vanilla.aslushnikov.com/?Target.setDiscoverTargets
            $cmd = [
                'id' => 1,
                'method' => 'Target.setDiscoverTargets',
                'params' => [
                    'discover' => true,
                ],
            ]; 
            $actual = $devTools->command($cmd);

            var_dump($actual);

            $expectedMethod = 'Target.targetCreated';
            $this->assertSame($expectedMethod, $actual['method']);    
        } finally {
            $tab->close();
        }
    }

    /**
     * This test case emulates the network tracing flow explained at the following blog.
     * @link https://medium.com/swlh/chrome-dev-tools-protocol-2d0ef2baf4bf
     */
    public function testDevTools_navigatePage(): void
    {
        try {
            $cdp = new Cdp('127.0.0.1', '9222');
            $tab = $cdp->open();

            $devTools = new DevToolsClient($tab->debuggerUrl);

            // https://vanilla.aslushnikov.com/?Network.enable
            $cmd = [
                'id' => 1,
                'method' => 'Network.enable',
                'params' => new stdClass, // all optionals
            ];
            $actual = $devTools->command($cmd);
            $this->assertSame(1, $actual['id']);

            // https://vanilla.aslushnikov.com/?Network.requestWillBeSent
            $devTools->addListener('Network.requestWillBeSent', function($params) {
                $request = $params['request'];
                $url = $request['url'];
                $method = $request['method'];
                echo "browser sent: {$method} {$url}\n";
            });
            
            // https://vanilla.aslushnikov.com/?Page.enable
            $cmd = [
                'id' => 1,
                'method' => 'Page.enable',
            ];
            $actual = $devTools->command($cmd);
            $this->assertSame(1, $actual['id']);

            // https://vanilla.aslushnikov.com/?Page.navigate
            $cmd = [
                'id' => 2,
                'method' => 'Page.navigate',
                'params' => [
                    'url' => 'https://autify.com',
                ],
            ];
            
            $actual = $devTools->command($cmd);
            $this->assertSame(2, $actual['id']);

            $devTools->receiveUntil(2);
        } finally {
            $tab->close();
        }
    }

    /**
     * This test case emulates the counting image flow explained at the following blog.
     * @link https://medium.com/swlh/chrome-dev-tools-protocol-2d0ef2baf4bf
     */
    public function testDevTools_countAllMimeType(): void
    {
        try {
            $cdp = new Cdp('127.0.0.1', '9222');
            $tab = $cdp->open();

            $devTools = new DevToolsClient($tab->debuggerUrl);

            // https://vanilla.aslushnikov.com/?Network.enable
            $cmd = [
                'id' => 1,
                'method' => 'Network.enable',
                'params' => new stdClass, // all optionals
            ];
            $actual = $devTools->command($cmd);
            $this->assertSame(1, $actual['id']);

            $actualResponse = new class {
                public array $responses = [];

                public function listener($params) {
                    $response = $params['response'];
                    $this->responses[] = $response;
                }
            };
            // https://vanilla.aslushnikov.com/?Network.responseReceived
            $devTools->addListener('Network.responseReceived', $actualResponse->listener(...));
            
            // https://vanilla.aslushnikov.com/?Page.enable
            $cmd = [
                'id' => 1,
                'method' => 'Page.enable',
            ];
            $actual = $devTools->command($cmd);
            $this->assertSame(1, $actual['id']);

            // https://vanilla.aslushnikov.com/?Page.navigate
            $cmd = [
                'id' => 2,
                'method' => 'Page.navigate',
                'params' => [
                    'url' => 'https://autify.com',
                ],
            ];
            $actual = $devTools->command($cmd);
            $this->assertSame(2, $actual['id']);

            $devTools->receiveUntil(2);

            $groupByMimeTypes = [];
            foreach ($actualResponse->responses as $response) {
                $mimeType = $response['mimeType'];
                $groupByMimeTypes[$mimeType][] = $response;
            }
            
            foreach ($groupByMimeTypes as $mimeType => $responses) {
                $count = count($responses);
                echo "mimeType: {$mimeType} count {$count}\n";
            }
        } finally {
            $tab->close();
        }
    }

    public function testDevTools_captureScreenshot(): void
    {
        try {
            $cdp = new Cdp('127.0.0.1', '9222');
            $tab = $cdp->open('https://autify.com');

            $devTools = new DevToolsClient($tab->debuggerUrl);
            
            // https://vanilla.aslushnikov.com/?Page.enable
            $cmd = [
                'id' => 1,
                'method' => 'Page.enable',
            ];
            $actual = $devTools->command($cmd);
            $this->assertSame(1, $actual['id']);

            /**
             * @link https://vanilla.aslushnikov.com/?Page.domContentEventFired
             *   ["timestamp"]=> float(67085.527266)
             */
            $eventResponse = $devTools->waitFor('Page.domContentEventFired', 3);

            // https://vanilla.aslushnikov.com/?Page.captureScreenshot
            $cmd = [
                'id' => 2,
                'method' => 'Page.captureScreenshot',
            ];
            $actual = $devTools->command($cmd);
            $this->assertSame(2, $actual['id']);
            // Base64-encoded image data
            $decoded = base64_decode($actual['result']['data'], true);
            if (!$decoded) {
                $this->fail('cannot decode a base64-encoded screenshot string');
            }

            file_put_contents(__DIR__ . '/../tmp/autify_com_screenshot_non_LCP.png', $decoded);
        } finally {
            $tab->close();
        }
    }

    public function testDevTools_captureScreenshot_LCP(): void
    {
        try {
            $cdp = new Cdp('127.0.0.1', '9222');
            $tab = $cdp->open();

            $devTools = new DevToolsClient($tab->debuggerUrl);
            
            // https://vanilla.aslushnikov.com/?PerformanceTimeline.enable
            $cmd = [
                'id' => 1,
                'method' => 'PerformanceTimeline.enable',
                'params' => [
                    // https://w3c.github.io/timing-entrytypes-registry/#registry
                    'eventTypes' => ['largest-contentful-paint'],
                ],
            ];
            $actual = $devTools->command($cmd);
            $this->assertSame(1, $actual['id']);

            // Go to a heavy page.
            $cmd = [
                'id' => 4,
                'method' => 'Page.navigate',
                'params' => [
                    'url' => 'https://autify.com',
                ],
            ];
            $actual = $devTools->command($cmd);
            $this->assertSame(4, $actual['id']);

            /**
             * @link https://vanilla.aslushnikov.com/?PerformanceTimeline.timelineEventAdded
             */
            $devTools->waitFor('PerformanceTimeline.timelineEventAdded', 3);

            // https://vanilla.aslushnikov.com/?Page.captureScreenshot
            $cmd = [
                'id' => 5,
                'method' => 'Page.captureScreenshot',
            ];
            $actual = $devTools->command($cmd);
            $this->assertSame(5, $actual['id']);
            // Base64-encoded image data
            $decoded = base64_decode($actual['result']['data'], true);
            if (!$decoded) {
                $this->fail('cannot decode a base64-encoded screenshot string');
            }

            file_put_contents(__DIR__ . '/../tmp/autify_com_screenshot_after_LCP.png', $decoded);
        } finally {
            $tab->close();
        }
    }
}
