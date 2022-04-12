<?php

namespace Yandex\Allure\Adapter\Fixtures;

use Yandex\Allure\Adapter\Event\StepEvent;
use Yandex\Allure\Adapter\Model\Entity;
use Yandex\Allure\Adapter\Model\Step;

class GenericStepEvent implements StepEvent
{
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function process(Entity $context)
    {
        if ($context instanceof Step) {
            $context->setName($this->name);
        }
    }
}
