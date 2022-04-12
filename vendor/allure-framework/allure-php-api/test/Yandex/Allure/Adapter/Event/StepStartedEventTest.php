<?php

namespace Yandex\Allure\Adapter\Event;

use PHPUnit\Framework\TestCase;
use Yandex\Allure\Adapter\Model\Status;
use Yandex\Allure\Adapter\Model\Step;

class StepStartedEventTest extends TestCase
{
    public function testEvent(): void
    {
        $step = new Step();
        $stepName = 'step-name';
        $stepTitle = 'step-title';
        $event = new StepStartedEvent($stepName);
        $event->withTitle($stepTitle);
        $event->process($step);
        $this->assertEquals(Status::PASSED, $step->getStatus());
        $this->assertEquals($stepTitle, $step->getTitle());
        $this->assertNotEmpty($step->getStart());
        $this->assertEquals($stepName, $step->getName());
        $this->assertEmpty($step->getStop());
        $this->assertEmpty($step->getSteps());
        $this->assertEmpty($step->getAttachments());
    }
}
