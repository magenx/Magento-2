<?php

namespace Yandex\Allure\Adapter\Event;

use PHPUnit\Framework\TestCase;
use Yandex\Allure\Adapter\Model\Step;

class StepFinishedEventTest extends TestCase
{
    public function testEvent(): void
    {
        $step = new Step();
        $event = new StepFinishedEvent();
        $event->process($step);
        $this->assertNotEmpty($step->getStop());
    }
}
