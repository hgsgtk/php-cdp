<?php

declare(strict_types=1);

use PhpCdp\Cdp;
use PhpCdp\DevToolsClient;

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
    
            $devToolsClient = new DevToolsClient($tab->debuggerUrl);
            $devToolsClient->ping();
    
            $cmd = new \stdClass();
            $cmd->id = 1;
            $cmd->method = 'Target.setDiscoverTargets'; // method
            $params = new \stdClass();
            $params->discover = true;
            $cmd->params = $params;
            
            $actual = $devToolsClient->command($cmd);
            $expectedMethod = 'Target.targetCreated';
            $this->assertSame($expectedMethod, $actual['method']);    
        } finally {
            $tab->close();
        }
    }
}
