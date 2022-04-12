<?php

namespace Yandex\Allure\Adapter\Event;

use Yandex\Allure\Adapter\Model\Parameter;
use Yandex\Allure\Adapter\Model\ParameterKind;
use Yandex\Allure\Adapter\Model\TestCase;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;

class AddParameterEventTest extends PhpUnitTestCase
{
    public function testEvent(): void
    {
        $parameterName = 'test-name';
        $parameterValue = 'test-value';
        $parameterKind = ParameterKind::ARGUMENT;
        $event = new AddParameterEvent($parameterName, $parameterValue, $parameterKind);
        $testCase = new TestCase();
        $event->process($testCase);
        $this->assertEquals(1, sizeof($testCase->getParameters()));
        $parameters = $testCase->getParameters();
        $parameter = array_pop($parameters);
        $this->assertTrue(
            ($parameter instanceof Parameter) &&
            ($parameter->getName() === $parameterName) &&
            ($parameter->getValue() === $parameterValue) &&
            ($parameter->getKind() === $parameterKind)
        );
    }
}
