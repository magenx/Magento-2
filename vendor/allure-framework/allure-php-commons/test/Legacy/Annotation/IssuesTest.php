<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Legacy\Annotation;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Test\Attribute\AnnotationTestTrait;
use Qameta\Allure\Attribute\Issue;
use Yandex\Allure\Adapter\Annotation\Issues;

/**
 * @covers \Yandex\Allure\Adapter\Annotation\Issues
 */
class IssuesTest extends TestCase
{
    use AnnotationTestTrait;

    public function testGetIssueKeys_WithSingleValue_ReturnsSameValueInList(): void
    {
        $issues = $this->getIssuesInstance('demoWithSingleValue');
        self::assertSame(['a'], $issues->getIssueKeys());
    }

    public function testGetIssueKeys_WithTwoValues_ReturnsSameValuesInList(): void
    {
        $issues = $this->getIssuesInstance('demoWithTwoValues');
        self::assertSame(['a', 'b'], $issues->getIssueKeys());
    }

    public function testConvert_WithSingleValue_ResultHasSameValueInList(): void
    {
        $issues = $this->getIssuesInstance('demoWithSingleValue');
        $expectedList = [
            [
                'class' => Issue::class,
                'type' => 'issue',
                'value' => 'a',
            ],
        ];
        self::assertSame($expectedList, $this->exportLinks(...$issues->convert()));
    }

    public function testConvert_WithTwoValues_ResultHasSameValuesInList(): void
    {
        $issues = $this->getIssuesInstance('demoWithTwoValues');
        $expectedList = [
            [
                'class' => Issue::class,
                'type' => 'issue',
                'value' => 'a',
            ],
            [
                'class' => Issue::class,
                'type' => 'issue',
                'value' => 'b',
            ],
        ];
        self::assertSame($expectedList, $this->exportLinks(...$issues->convert()));
    }

    /**
     * @Issues("a")
     */
    protected function demoWithSingleValue(): void
    {
    }

    /**
     * @Issues("a","b")
     */
    protected function demoWithTwoValues(): void
    {
    }

    /**
     * @psalm-suppress DeprecatedClass
     */
    private function getIssuesInstance(string $methodName): Issues
    {
        return $this->getLegacyAttributeInstance(Issues::class, $methodName);
    }
}
