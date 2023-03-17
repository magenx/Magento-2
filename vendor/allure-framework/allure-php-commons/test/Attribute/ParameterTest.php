<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Attribute;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Attribute\Parameter;
use Qameta\Allure\Attribute\ParameterMode;

/**
 * @covers \Qameta\Allure\Attribute\Parameter
 * @covers \Qameta\Allure\Attribute\AbstractParameter
 */
class ParameterTest extends TestCase
{
    use AnnotationTestTrait;

    public function testGetName_WithVNameReturnsSameString(): void
    {
        $parameter = $this->getParameterInstance('demoWithNameAndValue');
        self::assertSame('a', $parameter->getName());
    }

    public function testGetValue_WithValue_ReturnsSameString(): void
    {
        $parameter = $this->getParameterInstance('demoWithNameAndValue');
        self::assertSame('b', $parameter->getValue());
    }

    public function testGetExcluded_WithoutExcluded_ReturnsNull(): void
    {
        $parameter = $this->getParameterInstance('demoWithNameAndValue');
        self::assertNull($parameter->getExcluded());
    }

    public function testGetExcluded_WithExcluded_ReturnsTrue(): void
    {
        $parameter = $this->getParameterInstance('demoWithExcluded');
        self::assertTrue($parameter->getExcluded());
    }

    public function testGetMode_WithoutMode_ReturnsNull(): void
    {
        $parameter = $this->getParameterInstance('demoWithNameAndValue');
        self::assertNull($parameter->getMode());
    }

    public function testGetMode_WithMode_ReturnsSameMode(): void
    {
        $parameter = $this->getParameterInstance('demoWithMode');
        self::assertSame('hidden', $parameter->getMode());
    }

    #[Parameter("a", "b")]
    protected function demoWithNameAndValue(): void
    {
    }

    #[Parameter("a", "b", excluded: true)]
    protected function demoWithExcluded(): void
    {
    }

    #[Parameter("a", "b", mode: ParameterMode::HIDDEN)]
    protected function demoWithMode(): void
    {
    }

    private function getParameterInstance(string $methodName): Parameter
    {
        return $this->getAttributeInstance(Parameter::class, $methodName);
    }
}
