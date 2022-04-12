<?php

namespace Yandex\Allure\Adapter\Support;

use PHPUnit\Framework\TestCase;

class UtilsTest extends TestCase
{
    use Utils;

    public function testGetTimestamp(): void
    {
        $timestamp = self::getTimestamp();
        $this->assertTrue(is_float($timestamp));
        $this->assertGreaterThan(0, $timestamp);
    }

    public function testGenerateUUID(): void
    {
        $uuid1 = self::generateUUID();
        $uuid2 = self::generateUUID();
        $this->assertTrue(is_string($uuid1));
        $this->assertNotEquals($uuid1, $uuid2);
    }
}
