<?php

namespace Yandex\Allure\Adapter\Event;

use Yandex\Allure\Adapter\Model\Status;

class TestCaseFailedEventTest extends TestCaseStatusChangedEventTest
{
    protected function getTestedStatus(): string
    {
        return Status::FAILED;
    }

    protected function getTestCaseStatusChangedEvent(): TestCaseStatusChangedEvent
    {
        return new TestCaseFailedEvent();
    }
}
