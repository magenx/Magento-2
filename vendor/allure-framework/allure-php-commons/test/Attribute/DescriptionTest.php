<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Attribute;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Attribute\Description;

/**
 * @covers \Qameta\Allure\Attribute\Description
 * @covers \Qameta\Allure\Attribute\AbstractDescription
 */
class DescriptionTest extends TestCase
{
    use AnnotationTestTrait;

    public function testGetValue_WithValue_ReturnsSameString(): void
    {
        $description = $this->getDescriptionInstance('demoWithDefaultType');
        self::assertSame('a', $description->getValue());
    }

    public function testIsHtml_WithoutHtmlFlag_ReturnsFalse(): void
    {
        $description = $this->getDescriptionInstance('demoWithDefaultType');
        self::assertFalse($description->isHtml());
    }

    public function testIsHtml_WithFalseHtmlFlag_ReturnsFalse(): void
    {
        $description = $this->getDescriptionInstance('demoWithMarkdownType');
        self::assertFalse($description->isHtml());
    }

    public function testIsHtml_WithTrueHtmlFlag_ReturnsTrue(): void
    {
        $description = $this->getDescriptionInstance('demoWithHtmlType');
        self::assertTrue($description->isHtml());
    }

    #[Description('a')]
    protected function demoWithDefaultType(): void
    {
    }

    #[Description('a', false)]
    protected function demoWithMarkdownType(): void
    {
    }

    #[Description('a', true)]
    protected function demoWithHtmlType(): void
    {
    }

    private function getDescriptionInstance(string $methodName): Description
    {
        return $this->getAttributeInstance(Description::class, $methodName);
    }
}
