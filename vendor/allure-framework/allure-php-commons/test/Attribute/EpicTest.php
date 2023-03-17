<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Attribute;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Attribute\Epic;

/**
 * @covers \Qameta\Allure\Attribute\Epic
 * @covers \Qameta\Allure\Attribute\AbstractLabel
 */
class EpicTest extends TestCase
{
    use AnnotationTestTrait;

    public function testGetName_Always_ReturnsMatchingValue(): void
    {
        $epic = $this->getEpicInstance('demoWithValue');
        self::assertSame('epic', $epic->getName());
    }

    public function testGetValue_WithValue_ReturnsSameString(): void
    {
        $epic = $this->getEpicInstance('demoWithValue');
        self::assertSame('a', $epic->getValue());
    }

    #[Epic("a")]
    protected function demoWithValue(): void
    {
    }

    private function getEpicInstance(string $methodName): Epic
    {
        return $this->getAttributeInstance(Epic::class, $methodName);
    }
}
