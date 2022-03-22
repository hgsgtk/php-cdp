<?php

declare(strict_types=1);

use PhpCdp\Client;

final class ExampleTest extends \PHPUnit\Framework\TestCase
{
    public function testGotoPage(): void
    {
        $client = new Client();
        $tab = $client->openTab();
        $this->assertTrue(true);
    }
}
