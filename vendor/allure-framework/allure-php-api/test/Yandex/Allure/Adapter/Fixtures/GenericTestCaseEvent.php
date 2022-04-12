<?php

namespace Yandex\Allure\Adapter\Fixtures;

use Yandex\Allure\Adapter\Event\TestCaseEvent;
use Yandex\Allure\Adapter\Model\Entity;
use Yandex\Allure\Adapter\Model\TestCase;

class GenericTestCaseEvent implements TestCaseEvent
{
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function process(Entity $context)
    {
        if ($context instanceof TestCase) {
            $context->setName($this->name);
        }
    }
}
