<?php

namespace Yandex\Allure\Adapter\Event;

use PHPUnit\Framework\TestCase;
use Yandex\Allure\Adapter\Model\TestSuite;

class TestSuiteFinishedEventTest extends TestCase
{
    public function testEvent(): void
    {
        $testSuite = new TestSuite();
        $event = new TestSuiteFinishedEvent('some-uuid');
        $event->process($testSuite);
        $this->assertNotEmpty($testSuite->getStop());
    }
}
