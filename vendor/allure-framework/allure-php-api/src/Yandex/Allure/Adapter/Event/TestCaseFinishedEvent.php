<?php

namespace Yandex\Allure\Adapter\Event;

use Yandex\Allure\Adapter\Model\Entity;
use Yandex\Allure\Adapter\Model\TestCase;
use Yandex\Allure\Adapter\Support\Utils;

class TestCaseFinishedEvent implements TestCaseEvent
{
    use Utils;

    public function process(Entity $context)
    {
        if ($context instanceof TestCase) {
            $context->setStop(self::getTimestamp());
        }
    }
}
