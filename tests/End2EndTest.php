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

    public function testDevTools_navigatePage(): void
    {
        try {
            $cdp = new Cdp('127.0.0.1', '9222');
            $tab = $cdp->open();

            $devTools = new DevToolsClient($tab->debuggerUrl);
            
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
            var_dump($actual);

        } finally {
            sleep(3);
            $tab->close();
        }
    }
}
