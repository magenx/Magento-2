<?php

namespace Yandex\Allure\Adapter\Event;

use Yandex\Allure\Adapter\Model\Status;

class TestCaseBrokenEvent extends TestCaseStatusChangedEvent
{
    /**
     * @return string
     */
    protected function getStatus()
    {
        return Status::BROKEN;
    }
}
