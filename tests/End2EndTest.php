<?php

declare(strict_types=1);

use PhpCdp\Cdp;

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
}
