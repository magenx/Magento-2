<?php

namespace Yandex\Allure\Adapter\Event;

use Yandex\Allure\Adapter\Model\Status;

class TestCaseBrokenEventTest extends TestCaseStatusChangedEventTest
{
    protected function getTestedStatus(): string
    {
        return Status::BROKEN;
    }

    protected function getTestCaseStatusChangedEvent(): TestCaseStatusChangedEvent
    {
        return new TestCaseBrokenEvent();
    }
}
