<?php

namespace Yandex\Allure\Adapter\Event;

use Yandex\Allure\Adapter\Model\Entity;

interface Event
{
    public function process(Entity $context);
}
