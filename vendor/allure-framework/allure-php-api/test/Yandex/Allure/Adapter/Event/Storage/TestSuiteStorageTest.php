<?php

namespace Yandex\Allure\Adapter\Event\Storage;

use PHPUnit\Framework\TestCase;

class TestSuiteStorageTest extends TestCase
{
    public function testLifecycle(): void
    {
        $storage = new TestSuiteStorage();
        $uuid = 'some-uuid';
        $name = 'some-name';
        $testSuite = $storage->get($uuid);
        $this->assertEmpty($testSuite->getName());
        $testSuite->setName($name);
        $this->assertEquals($name, $storage->get($uuid)->getName());

        $storage->remove($uuid);
        $this->assertEmpty($storage->get($uuid)->getName());
    }
}
