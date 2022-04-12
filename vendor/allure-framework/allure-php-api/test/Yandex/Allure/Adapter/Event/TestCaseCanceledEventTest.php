<?php

namespace Yandex\Allure\Adapter\Event;

use Yandex\Allure\Adapter\Model\Status;

class TestCaseCanceledEventTest extends TestCaseStatusChangedEventTest
{
    protected function getTestedStatus(): string
    {
        return Status::CANCELED;
    }

    protected function getTestCaseStatusChangedEvent(): TestCaseStatusChangedEvent
    {
        return new TestCaseCanceledEvent();
    }
}
