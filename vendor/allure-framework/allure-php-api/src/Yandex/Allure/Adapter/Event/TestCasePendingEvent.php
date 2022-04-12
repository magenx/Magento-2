<?php

namespace Yandex\Allure\Adapter\Event;

use Yandex\Allure\Adapter\Model\Status;

class TestCasePendingEvent extends TestCaseStatusChangedEvent
{
    /**
     * @return string
     */
    protected function getStatus()
    {
        return Status::PENDING;
    }
}
