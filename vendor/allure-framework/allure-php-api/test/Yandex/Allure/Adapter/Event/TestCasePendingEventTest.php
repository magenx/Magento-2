<?php

namespace Yandex\Allure\Adapter\Event;

use Yandex\Allure\Adapter\Model\Status;

class TestCasePendingEventTest extends TestCaseStatusChangedEventTest
{
    protected function getTestedStatus(): string
    {
        return Status::PENDING;
    }

    protected function getTestCaseStatusChangedEvent(): TestCaseStatusChangedEvent
    {
        return new TestCasePendingEvent();
    }
}
