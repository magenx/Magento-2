<?php

namespace Yandex\Allure\Adapter\Event;

use Yandex\Allure\Adapter\Model\Entity;

class ClearTestCaseStorageEvent implements TestCaseEvent
{
    public function process(Entity $context)
    {
    }
}
