<?php

namespace Yandex\Allure\Adapter\Event;

interface TestSuiteEvent extends Event
{
    /**
     * @return string
     */
    public function getUuid();
}
