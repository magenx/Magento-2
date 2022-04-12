<?php

namespace Yandex\Allure\Adapter\Event;

use PHPUnit\Framework\TestCase as PhpUnitTestCase;
use Yandex\Allure\Adapter\Model\TestCase;

class TestCaseFinishedEventTest extends PhpUnitTestCase
{
    public function testEvent(): void
    {
        $testCase = new TestCase();
        $event = new TestCaseFinishedEvent();
        $event->process($testCase);
        $this->assertNotEmpty($testCase->getStop());
    }
}
