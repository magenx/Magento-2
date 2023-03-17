<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Legacy\Annotation;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Test\Attribute\AnnotationTestTrait;
use Yandex\Allure\Adapter\Annotation\Title;

/**
 * @covers \Yandex\Allure\Adapter\Annotation\Title
 */
class TitleTest extends TestCase
{
    use AnnotationTestTrait;

    public function testValue_WithValue_ReturnsSameValue(): void
    {
        $title = $this->getTitleInstance('demoWithValue');
        self::assertSame('a', $title->value);
    }

    public function testConvert_WithValue_ResultHasSameValue(): void
    {
        $title = $this->getTitleInstance('demoWithValue');
        self::assertSame('a', $title->convert()->getValue());
    }

    /**
     * @Title("a")
     */
    protected function demoWithValue(): void
    {
    }

    /**
     * @psalm-suppress DeprecatedClass
     */
    private function getTitleInstance(string $methodName): Title
    {
        return $this->getLegacyAttributeInstance(Title::class, $methodName);
    }
}
