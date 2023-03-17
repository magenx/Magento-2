<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Legacy\Annotation;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Test\Attribute\AnnotationTestTrait;
use Qameta\Allure\Attribute\TmsLink;
use Yandex\Allure\Adapter\Annotation\TestCaseId;

/**
 * @covers \Yandex\Allure\Adapter\Annotation\TestCaseId
 */
class TestCaseIdTest extends TestCase
{
    use AnnotationTestTrait;

    public function testGetTestCaseIds_WithSingleValue_ReturnsSameValueInList(): void
    {
        $testCaseId = $this->getTestCaseIdInstance('demoWithSingleValue');
        self::assertSame(['a'], $testCaseId->getTestCaseIds());
    }

    public function testGetTestCaseIds_WithTwoValues_ReturnsSameValuesInList(): void
    {
        $testCaseId = $this->getTestCaseIdInstance('demoWithTwoValues');
        self::assertSame(['a', 'b'], $testCaseId->getTestCaseIds());
    }

    public function testConvert_WithSingleValue_ResultHasSameValueInList(): void
    {
        $testCaseId = $this->getTestCaseIdInstance('demoWithSingleValue');
        $expectedList = [
            [
                'class' => TmsLink::class,
                'type' => 'tms',
                'value' => 'a',
            ],
        ];
        self::assertSame($expectedList, $this->exportLinks(...$testCaseId->convert()));
    }

    public function testConvert_WithTwoValues_ResultHasSameValuesInList(): void
    {
        $testCaseId = $this->getTestCaseIdInstance('demoWithTwoValues');
        $expectedList = [
            [
                'class' => TmsLink::class,
                'type' => 'tms',
                'value' => 'a',
            ],
            [
                'class' => TmsLink::class,
                'type' => 'tms',
                'value' => 'b',
            ],
        ];
        self::assertSame($expectedList, $this->exportLinks(...$testCaseId->convert()));
    }

    /**
     * @TestCaseId("a")
     */
    protected function demoWithSingleValue(): void
    {
    }

    /**
     * @TestCaseId("a","b")
     */
    protected function demoWithTwoValues(): void
    {
    }

    /**
     * @psalm-suppress DeprecatedClass
     */
    private function getTestCaseIdInstance(string $methodName): TestCaseId
    {
        return $this->getLegacyAttributeInstance(TestCaseId::class, $methodName);
    }
}
