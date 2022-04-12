<?php

namespace Yandex\Allure\Adapter\Event;

use PHPUnit\Framework\TestCase;
use Yandex\Allure\Adapter\Model\Step;

abstract class StepStatusChangedEventTest extends TestCase
{
    abstract protected function getTestedStatus(): string;

    abstract protected function getStepEvent(): StepEvent;

    public function testEvent(): void
    {
        $step = new Step();
        $event = $this->getStepEvent();
        $event->process($step);
        $this->assertEquals($this->getTestedStatus(), $step->getStatus());
    }
}
