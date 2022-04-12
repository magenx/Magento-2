<?php

namespace Yandex\Allure\Adapter\Event;

use Yandex\Allure\Adapter\Model\Entity;
use Yandex\Allure\Adapter\Model\Step;
use Yandex\Allure\Adapter\Support\Utils;

class StepFinishedEvent implements StepEvent
{
    use Utils;

    public function process(Entity $context)
    {
        if ($context instanceof Step) {
            $context->setStop(self::getTimestamp());
        }
    }
}
