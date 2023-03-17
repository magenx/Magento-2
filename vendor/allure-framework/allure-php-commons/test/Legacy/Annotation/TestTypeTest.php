<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Legacy\Annotation;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Test\Attribute\AnnotationTestTrait;
use Yandex\Allure\Adapter\Annotation\TestType;

/**
 * @covers \Yandex\Allure\Adapter\Annotation\TestType
 */
class TestTypeTest extends TestCase
{
    use AnnotationTestTrait;

    public function testType_WithoutValue_ReturnsDefaultValue(): void
    {
        $testType = $this->getTitleInstance('demoWithoutValue');
        self::assertSame('screenshotDiff', $testType->type);
    }

    public function testType_WithValue_ReturnsSameValue(): void
    {
        $testType = $this->getTitleInstance('demoWithValue');
        self::assertSame('a', $testType->type);
    }

    /**
     * @TestType
     */
    protected function demoWithoutValue(): void
    {
    }

    /**
     * @TestType("a")
     */
    protected function demoWithValue(): void
    {
    }

    /**
     * @psalm-suppress DeprecatedClass
     */
    private function getTitleInstance(string $methodName): TestType
    {
        return $this->getLegacyAttributeInstance(TestType::class, $methodName);
    }
}
