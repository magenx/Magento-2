<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Legacy\Annotation;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Test\Attribute\AnnotationTestTrait;
use Yandex\Allure\Adapter\Annotation\Parameter;

/**
 * @covers \Yandex\Allure\Adapter\Annotation\Parameter
 */
class ParameterTest extends TestCase
{
    use AnnotationTestTrait;

    public function testName_WithName_ReturnsSameString(): void
    {
        $parameter = $this->getParameterInstance('demoWithNameAndValue');
        self::assertSame('a', $parameter->name);
    }

    public function testValue_WithValue_ReturnsSameString(): void
    {
        $parameter = $this->getParameterInstance('demoWithNameAndValue');
        self::assertSame('b', $parameter->value);
    }

    public function testConvert_WithName_ResultHasSameName(): void
    {
        $parameter = $this->getParameterInstance('demoWithNameAndValue');
        self::assertSame('a', $parameter->convert()->getName());
    }

    /**
     * @Parameter(name="a", value="b")
     */
    protected function demoWithNameAndValue(): void
    {
    }

    /**
     * @psalm-suppress DeprecatedClass
     */
    private function getParameterInstance(string $methodName): Parameter
    {
        return $this->getLegacyAttributeInstance(Parameter::class, $methodName);
    }
}
