<?php

namespace Yandex\Allure\Adapter\Event;

use Yandex\Allure\Adapter\Model\Status;

class StepCanceledEventTest extends StepStatusChangedEventTest
{
    protected function getTestedStatus(): string
    {
        return Status::CANCELED;
    }

    protected function getStepEvent(): StepEvent
    {
        return new StepCanceledEvent();
    }
}
