<?php

namespace Yandex\Allure\Adapter\Event;

use Yandex\Allure\Adapter\Model\Description;
use Yandex\Allure\Adapter\Model\DescriptionType;
use Yandex\Allure\Adapter\Model\Label;
use Yandex\Allure\Adapter\Model\LabelType;
use Yandex\Allure\Adapter\Model\Parameter;
use Yandex\Allure\Adapter\Model\ParameterKind;
use Yandex\Allure\Adapter\Model\Status;
use Yandex\Allure\Adapter\Model\TestCase;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;

class TestCaseStartedEventTest extends PhpUnitTestCase
{
    public function testEvent(): void
    {
        $testCase = new TestCase();
        $uuid = 'test-uuid';
        $testCaseName = 'test-case-name';
        $testCaseTitle = 'test-case-title';
        $testCaseDescriptionValue = 'test-case-description-value';
        $testCaseDescriptionType = DescriptionType::TEXT;
        $testCaseLabelValue = 'test-case-label-value';
        $testCaseLabelName = LabelType::STORY;
        $testCaseParameterName = 'test-case-parameter-name';
        $testCaseParameterValue = 'test-case-parameter-value';
        $testCaseParameterKind = ParameterKind::ARGUMENT;
        $event = new TestCaseStartedEvent($uuid, $testCaseName);
        $event
            ->withTitle($testCaseTitle)
            ->withDescription(new Description($testCaseDescriptionType, $testCaseDescriptionValue))
            ->withLabels([new Label($testCaseLabelName, $testCaseLabelValue)])
            ->withParameters([new Parameter($testCaseParameterName, $testCaseParameterValue, $testCaseParameterKind)]);
        $event->process($testCase);

        $this->assertEquals(Status::PASSED, $testCase->getStatus());
        $this->assertEquals($testCaseTitle, $testCase->getTitle());
        $this->assertNotEmpty($testCase->getStart());
        $this->assertEquals($testCaseName, $testCase->getName());
        $this->assertNotEmpty($testCase->getDescription());
        $this->assertEquals($testCaseDescriptionValue, $testCase->getDescription()->getValue());
        $this->assertEquals($testCaseDescriptionType, $testCase->getDescription()->getType());
        $this->assertEquals(1, sizeof($testCase->getLabels()));
        $labels = $testCase->getLabels();
        $label = array_pop($labels);
        $this->assertTrue(
            ($label instanceof Label) &&
            ($label->getName() === $testCaseLabelName) &&
            ($label->getValue() === $testCaseLabelValue)
        );
        $this->assertEquals(1, sizeof($testCase->getParameters()));
        $parameters = $testCase->getParameters();
        $parameter = array_pop($parameters);
        $this->assertTrue(
            ($parameter instanceof Parameter) &&
            ($parameter->getName() === $testCaseParameterName) &&
            ($parameter->getValue() === $testCaseParameterValue) &&
            ($parameter->getKind() === $testCaseParameterKind)
        );
        $this->assertEmpty($testCase->getStop());
        $this->assertEmpty($testCase->getSteps());
        $this->assertEmpty($testCase->getAttachments());
    }
}
