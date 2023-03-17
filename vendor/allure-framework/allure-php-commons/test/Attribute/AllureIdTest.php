<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Attribute;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Attribute\AllureId;

/**
 * @covers \Qameta\Allure\Attribute\AllureId
 * @covers \Qameta\Allure\Attribute\AbstractLabel
 */
class AllureIdTest extends TestCase
{
    use AnnotationTestTrait;

    public function testGetValue_WithValue_ReturnsSameValue(): void
    {
        $allureId = $this->getAllureIdInstance('demoWithValue');
        self::assertSame('a', $allureId->getValue());
    }

    #[AllureId("a")]
    protected function demoWithValue(): void
    {
    }

    private function getAllureIdInstance(string $methodName): AllureId
    {
        return $this->getAttributeInstance(AllureId::class, $methodName);
    }
}
