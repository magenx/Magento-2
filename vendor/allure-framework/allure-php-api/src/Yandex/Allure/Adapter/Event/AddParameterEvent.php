<?php

namespace Yandex\Allure\Adapter\Event;

use Yandex\Allure\Adapter\Model\Entity;
use Yandex\Allure\Adapter\Model\Parameter;
use Yandex\Allure\Adapter\Model\ParameterKind;
use Yandex\Allure\Adapter\Model\TestCase;

class AddParameterEvent implements TestCaseEvent
{
    private $name;
    private $value;
    private $kind;

    public function __construct($name, $value, $kind = ParameterKind::SYSTEM_PROPERTY)
    {
        $this->name = $name;
        $this->value = $value;
        $this->kind = $kind;
    }

    public function process(Entity $context)
    {
        if ($context instanceof TestCase) {
            $context->addParameter(new Parameter($this->name, $this->value, $this->kind));
        }
    }
}
