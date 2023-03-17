<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Legacy\Annotation;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Test\Attribute\AnnotationTestTrait;
use Qameta\Allure\Attribute\Feature;
use Yandex\Allure\Adapter\Annotation\Features;

/**
 * @covers \Yandex\Allure\Adapter\Annotation\Features
 */
class FeaturesTest extends TestCase
{
    use AnnotationTestTrait;

    public function testGetFeatureNames_WithSingleValue_ReturnsSameValueInList(): void
    {
        $features = $this->getFeaturesInstance('demoWithSingleValue');
        self::assertSame(['a'], $features->getFeatureNames());
    }

    public function testGetFeatureNames_WithTwoValues_ReturnsSameValuesInList(): void
    {
        $features = $this->getFeaturesInstance('demoWithTwoValues');
        self::assertSame(['a', 'b'], $features->getFeatureNames());
    }

    public function testConvert_WithSingleValue_ResultHasSameValueInList(): void
    {
        $features = $this->getFeaturesInstance('demoWithSingleValue');
        $expectedList = [
            [
                'class' => Feature::class,
                'name' => 'feature',
                'value' => 'a',
            ],
        ];
        self::assertSame($expectedList, $this->exportLabels(...$features->convert()));
    }

    public function testConvert_WithTwoValues_ResultHasSameValuesInList(): void
    {
        $features = $this->getFeaturesInstance('demoWithTwoValues');
        $expectedList = [
            [
                'class' => Feature::class,
                'name' => 'feature',
                'value' => 'a',
            ],
            [
                'class' => Feature::class,
                'name' => 'feature',
                'value' => 'b',
            ],
        ];
        self::assertSame($expectedList, $this->exportLabels(...$features->convert()));
    }

    /**
     * @Features("a")
     */
    protected function demoWithSingleValue(): void
    {
    }

    /**
     * @Features("a","b")
     */
    protected function demoWithTwoValues(): void
    {
    }

    /**
     * @psalm-suppress DeprecatedClass
     */
    private function getFeaturesInstance(string $methodName): Features
    {
        return $this->getLegacyAttributeInstance(Features::class, $methodName);
    }
}
