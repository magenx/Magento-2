<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Legacy\Annotation;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Test\Attribute\AnnotationTestTrait;
use Yandex\Allure\Adapter\Annotation\AllureId;

/**
 * @covers \Yandex\Allure\Adapter\Annotation\AllureId
 */
class AllureIdTest extends TestCase
{
    use AnnotationTestTrait;

    public function testValue_WithValue_ReturnsSameValue(): void
    {
        $allureId = $this->getAllureIdInstance('demoWithValue');
        self::assertSame('a', $allureId->value);
    }

    public function testConvert_WithValue_ResultHasSameValue(): void
    {
        $allureId = $this->getAllureIdInstance('demoWithValue');
        self::assertSame('a', $allureId->convert()->getValue());
    }

    /**
     * @AllureId("a")
     */
    protected function demoWithValue(): void
    {
    }

    /**
     * @psalm-suppress DeprecatedClass
     */
    private function getAllureIdInstance(string $methodName): AllureId
    {
        return $this->getLegacyAttributeInstance(AllureId::class, $methodName);
    }
}
