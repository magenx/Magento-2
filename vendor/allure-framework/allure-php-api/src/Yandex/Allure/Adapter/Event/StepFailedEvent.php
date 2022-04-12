<?php

namespace Yandex\Allure\Adapter\Event;

use Yandex\Allure\Adapter\Model\Entity;
use Yandex\Allure\Adapter\Model\Status;
use Yandex\Allure\Adapter\Model\Step;

class StepFailedEvent implements StepEvent
{
    public function process(Entity $context)
    {
        if ($context instanceof Step) {
            $context->setStatus(Status::FAILED);
        }
    }
}
