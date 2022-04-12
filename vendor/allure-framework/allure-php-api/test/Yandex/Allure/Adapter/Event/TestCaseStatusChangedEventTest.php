<?php

namespace Yandex\Allure\Adapter\Event;

use Exception;
use Yandex\Allure\Adapter\Model\TestCase;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;

abstract class TestCaseStatusChangedEventTest extends PhpUnitTestCase
{
    abstract protected function getTestedStatus(): string;

    abstract protected function getTestCaseStatusChangedEvent(): TestCaseStatusChangedEvent;

    public function testEvent(): void
    {
        $testMessage = 'test-message';
        $testCase = new TestCase();
        $event = $this->getTestCaseStatusChangedEvent();
        $event->withMessage($testMessage)->withException(new Exception());
        $event->process($testCase);

        $this->assertEquals($this->getTestedStatus(), $testCase->getStatus());
        $this->assertEquals($testMessage, $testCase->getFailure()->getMessage());
        $this->assertNotEmpty($testCase->getFailure()->getStackTrace());
    }
}
