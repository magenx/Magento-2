<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Attribute;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Attribute\Label;

/**
 * @covers \Qameta\Allure\Attribute\Label
 * @covers \Qameta\Allure\Attribute\AbstractLabel
 */
class LabelTest extends TestCase
{
    use AnnotationTestTrait;

    public function testGetName_WithName_ReturnsSameString(): void
    {
        $label = $this->getLabelInstance('demoWithNameAndValue');
        self::assertSame('a', $label->getName());
    }

    public function testGetValue_WithoutValue_ReturnsNull(): void
    {
        $label = $this->getLabelInstance('demoWithoutValue');
        self::assertNull($label->getValue());
    }

    public function testGetValue_WithValue_ReturnsSameValue(): void
    {
        $label = $this->getLabelInstance('demoWithNameAndValue');
        self::assertSame('b', $label->getValue());
    }

    #[Label("a")]
    protected function demoWithoutValue(): void
    {
    }

    #[Label("a", "b")]
    protected function demoWithNameAndValue(): void
    {
    }

    private function getLabelInstance(string $methodName): Label
    {
        return $this->getAttributeInstance(Label::class, $methodName);
    }
}
