<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Attribute;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Attribute\DisplayName;

/**
 * @covers \Qameta\Allure\Attribute\DisplayName
 * @covers \Qameta\Allure\Attribute\AbstractDisplayName
 */
class DisplayNameTest extends TestCase
{
    use AnnotationTestTrait;

    public function testGetValue_WithValue_ReturnsSameValue(): void
    {
        $displayName = $this->getDisplayNameInstance('demoWithValue');
        self::assertSame('a', $displayName->getValue());
    }

    #[DisplayName("a")]
    protected function demoWithValue(): void
    {
    }

    private function getDisplayNameInstance(string $methodName): DisplayName
    {
        return $this->getAttributeInstance(DisplayName::class, $methodName);
    }
}
