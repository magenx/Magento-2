<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Legacy\Annotation;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Attribute\Parameter as QametaParameter;
use Qameta\Allure\Test\Attribute\AnnotationTestTrait;
use Yandex\Allure\Adapter\Annotation\Parameter;
use Yandex\Allure\Adapter\Annotation\Parameters;

use function array_map;

/**
 * @covers \Yandex\Allure\Adapter\Annotation\Parameters
 */
class ParametersTest extends TestCase
{
    use AnnotationTestTrait;

    public function testParameters_WithSingleParameter_ReturnsMatchingList(): void
    {
        $parameters = $this->getParametersInstance('demoWithSingleParameter');
        $expectedParameters = [
            ['name' => 'a', 'value' => 'b'],
        ];
        self::assertSame($expectedParameters, $this->exportLegacyParameters(...$parameters->parameters));
    }

    public function testLabels_WithTwoParameters_ReturnsMatchingList(): void
    {
        $parameters = $this->getParametersInstance('demoWithTwoParameters');
        $expectedParameters = [
            ['name' => 'a', 'value' => 'b'],
            ['name' => 'c', 'value' => 'd'],
        ];
        self::assertSame($expectedParameters, $this->exportLegacyParameters(...$parameters->parameters));
    }

    public function testConvert_WithSingleParameter_ReturnsMatchingList(): void
    {
        $parameters = $this->getParametersInstance('demoWithSingleParameter');
        $expectedParameters = [
            ['name' => 'a', 'value' => 'b'],
        ];
        self::assertSame($expectedParameters, $this->exportParameters(...$parameters->convert()));
    }

    public function testConvert_WithTwoParameters_ReturnsMatchingList(): void
    {
        $parameters = $this->getParametersInstance('demoWithTwoParameters');
        $expectedParameters = [
            ['name' => 'a', 'value' => 'b'],
            ['name' => 'c', 'value' => 'd'],
        ];
        self::assertSame($expectedParameters, $this->exportParameters(...$parameters->convert()));
    }

    /**
     * @Parameters(
     *     @Parameter(name="a", value="b")
     * )
     */
    protected function demoWithSingleParameter(): void
    {
    }

    /**
     * @Parameters(
     *     @Parameter(name="a", value="b"),
     *     @Parameter(name="c", value="d"),
     * )
     */
    protected function demoWithTwoParameters(): void
    {
    }

    /**
     * @psalm-suppress DeprecatedClass
     */
    private function exportLegacyParameter(Parameter $label): array
    {
        return [
            'name' => $label->name,
            'value' => $label->value,
        ];
    }

    /**
     * @psalm-suppress DeprecatedClass
     */
    private function exportLegacyParameters(Parameter ...$parameters): array
    {
        return array_map(
            [$this, 'exportLegacyParameter'],
            $parameters,
        );
    }

    private function exportParameter(QametaParameter $parameter): array
    {
        return [
            'name' => $parameter->getName(),
            'value' => $parameter->getValue(),
        ];
    }

    private function exportParameters(QametaParameter ...$parameters): array
    {
        return array_map(
            [$this, 'exportParameter'],
            $parameters,
        );
    }

    /**
     * @psalm-suppress DeprecatedClass
     */
    private function getParametersInstance(string $methodName): Parameters
    {
        return $this->getLegacyAttributeInstance(Parameters::class, $methodName);
    }
}
