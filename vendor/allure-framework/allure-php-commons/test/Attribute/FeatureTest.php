<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Attribute;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Attribute\Feature;

/**
 * @covers \Qameta\Allure\Attribute\Feature
 * @covers \Qameta\Allure\Attribute\AbstractLabel
 */
class FeatureTest extends TestCase
{
    use AnnotationTestTrait;

    public function testGetName_Always_ReturnsMatchingValue(): void
    {
        $feature = $this->getFeatureInstance('demoWithValue');
        self::assertSame('feature', $feature->getName());
    }

    public function testGetValue_WithValue_ReturnsSameString(): void
    {
        $feature = $this->getFeatureInstance('demoWithValue');
        self::assertSame('a', $feature->getValue());
    }

    #[Feature("a")]
    protected function demoWithValue(): void
    {
    }

    private function getFeatureInstance(string $methodName): Feature
    {
        return $this->getAttributeInstance(Feature::class, $methodName);
    }
}
