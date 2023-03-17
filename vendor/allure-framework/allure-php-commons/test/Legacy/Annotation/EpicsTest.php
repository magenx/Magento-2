<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Legacy\Annotation;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Test\Attribute\AnnotationTestTrait;
use Qameta\Allure\Attribute\Epic;
use Yandex\Allure\Adapter\Annotation\Epics;

/**
 * @covers \Yandex\Allure\Adapter\Annotation\Epics
 */
class EpicsTest extends TestCase
{
    use AnnotationTestTrait;

    public function testGetEpicNames_WithSingleValue_ReturnsSameValueInList(): void
    {
        $epics = $this->getEpicsInstance('demoWithSingleValue');
        self::assertSame(['a'], $epics->getEpicNames());
    }

    public function testGetEpicNames_WithTwoValues_ReturnsSameValuesInList(): void
    {
        $epics = $this->getEpicsInstance('demoWithTwoValues');
        self::assertSame(['a', 'b'], $epics->getEpicNames());
    }

    public function testConvert_WithSingleValue_ResultHasEpicWithSameNameInList(): void
    {
        $epics = $this->getEpicsInstance('demoWithSingleValue');
        $expectedList = [
            [
                'class' => Epic::class,
                'name' => 'epic',
                'value' => 'a',
            ],
        ];
        self::assertSame($expectedList, $this->exportLabels(...$epics->convert()));
    }

    public function testConvert_WithTwoValues_ResultHasEpicsWithSameNamesInList(): void
    {
        $epics = $this->getEpicsInstance('demoWithTwoValues');
        $expectedList = [
            [
                'class' => Epic::class,
                'name' => 'epic',
                'value' => 'a',
            ],
            [
                'class' => Epic::class,
                'name' => 'epic',
                'value' => 'b',
            ],
        ];
        self::assertSame($expectedList, $this->exportLabels(...$epics->convert()));
    }

    /**
     * @Epics("a")
     */
    protected function demoWithSingleValue(): void
    {
    }

    /**
     * @Epics("a","b")
     */
    protected function demoWithTwoValues(): void
    {
    }

    /**
     * @psalm-suppress DeprecatedClass
     */
    private function getEpicsInstance(string $methodName): Epics
    {
        return $this->getLegacyAttributeInstance(Epics::class, $methodName);
    }
}
