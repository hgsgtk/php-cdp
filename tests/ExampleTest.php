<?php

declare(strict_types=1);

use PhpCdp\Cdp;

final class ExampleTest extends \PHPUnit\Framework\TestCase
{
    public function testGotoPage(): void
    {
        $client = new Cdp('127.0.0.1', '9222');
        $tab = $client->tab();
        $this->assertTrue(true);
    }
}
