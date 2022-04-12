<?php

namespace Yandex\Allure\Adapter\Event;

use Yandex\Allure\Adapter\Model\Entity;
use Yandex\Allure\Adapter\Model\Status;
use Yandex\Allure\Adapter\Model\Step;
use Yandex\Allure\Adapter\Support\Utils;

class StepStartedEvent implements StepEvent
{
    use Utils;

    private $name;

    private $title;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function process(Entity $context)
    {
        if ($context instanceof Step) {
            $context->setName($this->name);
            $context->setStatus(Status::PASSED);
            $context->setStart(self::getTimestamp());
            $context->setTitle($this->title);
        }
    }

    public function withTitle($title)
    {
        $this->title = $title;

        return $this;
    }
}
