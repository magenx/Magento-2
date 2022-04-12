<?php

namespace Yandex\Allure\Adapter\Event;

use PHPUnit\Framework\TestCase;
use Yandex\Allure\Adapter\Model\Description;
use Yandex\Allure\Adapter\Model\DescriptionType;
use Yandex\Allure\Adapter\Model\Label;
use Yandex\Allure\Adapter\Model\LabelType;
use Yandex\Allure\Adapter\Model\TestSuite;

class TestSuiteStartedEventTest extends TestCase
{
    public function testEvent(): void
    {
        $testSuite = new TestSuite();
        $testSuiteName = 'test-suite-name';
        $testSuiteTitle = 'test-suite-title';
        $testSuiteDescriptionValue = 'test-suite-description-value';
        $testSuiteDescriptionType = DescriptionType::TEXT;
        $testSuiteLabelValue = 'test-suite-label-value';
        $testSuiteLabelName = LabelType::STORY;
        $event = new TestSuiteStartedEvent($testSuiteName);
        $event
            ->withTitle($testSuiteTitle)
            ->withDescription(new Description($testSuiteDescriptionType, $testSuiteDescriptionValue))
            ->withLabels(array(new Label($testSuiteLabelName, $testSuiteLabelValue)));
        $event->process($testSuite);

        $this->assertEquals($testSuiteTitle, $testSuite->getTitle());
        $this->assertNotEmpty($testSuite->getStart());
        $this->assertEquals($testSuiteName, $testSuite->getName());
        $this->assertNotEmpty($testSuite->getDescription());
        $this->assertEquals($testSuiteDescriptionValue, $testSuite->getDescription()->getValue());
        $this->assertEquals($testSuiteDescriptionType, $testSuite->getDescription()->getType());
        $this->assertEquals(1, sizeof($testSuite->getLabels()));
        $labels = $testSuite->getLabels();
        $label = array_pop($labels);
        $this->assertTrue(
            ($label instanceof Label) &&
            ($label->getName() === $testSuiteLabelName) &&
            ($label->getValue() === $testSuiteLabelValue)
        );
        $this->assertEmpty($testSuite->getStop());
    }
}
