<?php

namespace Yandex\Allure\Adapter\Event;

use Yandex\Allure\Adapter\Model\Status;

class StepFailedEventTest extends StepStatusChangedEventTest
{
    protected function getTestedStatus(): string
    {
        return Status::FAILED;
    }

    protected function getStepEvent(): StepEvent
    {
        return new StepFailedEvent();
    }
}
