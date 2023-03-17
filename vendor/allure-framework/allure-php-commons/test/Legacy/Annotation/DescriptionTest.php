<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Legacy\Annotation;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Test\Attribute\AnnotationTestTrait;
use Yandex\Allure\Adapter\Annotation\Description;
use Yandex\Allure\Adapter\Model\DescriptionType;

/**
 * @covers \Yandex\Allure\Adapter\Annotation\Description
 */
class DescriptionTest extends TestCase
{
    use AnnotationTestTrait;

    public function testValue_WithValue_ReturnsSameString(): void
    {
        $description = $this->getDescriptionInstance('demoWithoutType');
        self::assertSame('a', $description->value);
    }

    public function testType_WithoutType_ReturnsDefaultValue(): void
    {
        $description = $this->getDescriptionInstance('demoWithoutType');
        self::assertSame('text', $description->type);
    }

    public function testType_WithTextType_ReturnsTextValue(): void
    {
        $description = $this->getDescriptionInstance('demoWithTextType');
        self::assertSame('text', $description->type);
    }

    public function testType_WithMarkdownType_ReturnsMarkdownValue(): void
    {
        $description = $this->getDescriptionInstance('demoWithMarkdownType');
        self::assertSame('markdown', $description->type);
    }

    public function testType_WithHtmlType_ReturnsHtmlValue(): void
    {
        $description = $this->getDescriptionInstance('demoWithHtmlType');
        self::assertSame('html', $description->type);
    }

    public function testConvert_WithValue_ResultHasSameType(): void
    {
        $description = $this->getDescriptionInstance('demoWithoutType');
        self::assertSame('a', $description->convert()->getValue());
    }

    public function testConvert_WithoutType_ResultIsNotHtml(): void
    {
        $description = $this->getDescriptionInstance('demoWithoutType');
        self::assertFalse($description->convert()->isHtml());
    }

    public function testConvert_WithTextType_ResultIsNotHtml(): void
    {
        $description = $this->getDescriptionInstance('demoWithTextType');
        self::assertFalse($description->convert()->isHtml());
    }

    public function testConvert_WithMarkdownType_ResultIsNotHtml(): void
    {
        $description = $this->getDescriptionInstance('demoWithMarkdownType');
        self::assertFalse($description->convert()->isHtml());
    }

    public function testConvert_WithHtmlType_ResultIsHtml(): void
    {
        $description = $this->getDescriptionInstance('demoWithHtmlType');
        self::assertTrue($description->convert()->isHtml());
    }

    /**
     * @Description("a")
     */
    protected function demoWithoutType(): void
    {
    }

    /**
     * @Description("a", type=DescriptionType::TEXT)
     */
    protected function demoWithTextType(): void
    {
    }

    /**
     * @Description("a", type=DescriptionType::MARKDOWN)
     */
    protected function demoWithMarkdownType(): void
    {
    }

    /**
     * @Description("a", type=DescriptionType::HTML)
     */
    protected function demoWithHtmlType(): void
    {
    }

    /**
     * @psalm-suppress DeprecatedClass
     */
    private function getDescriptionInstance(string $methodName): Description
    {
        return $this->getLegacyAttributeInstance(Description::class, $methodName);
    }
}
