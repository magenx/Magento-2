<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Legacy\Annotation;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Test\Attribute\AnnotationTestTrait;
use Qameta\Allure\Attribute\Label as QametaLabel;
use Yandex\Allure\Adapter\Annotation\Label;

/**
 * @covers \Yandex\Allure\Adapter\Annotation\Label
 */
class LabelTest extends TestCase
{
    use AnnotationTestTrait;

    public function testName_WithName_ReturnsSameValue(): void
    {
        $label = $this->getLabelInstance('withSingleValue');
        self::assertSame('a', $label->name);
    }

    public function testValues_WithSingleValue_ReturnsSameValuesInList(): void
    {
        $label = $this->getLabelInstance('withSingleValue');
        self::assertSame(['b'], $label->values);
    }

    public function testValues_WithTwoValues_ReturnsSameValuesInList(): void
    {
        $label = $this->getLabelInstance('withTwoValues');
        self::assertSame(['b', 'c'], $label->values);
    }

    public function testConvert_WithSingleValue_ReturnsMatchingList(): void
    {
        $label = $this->getLabelInstance('withSingleValue');
        $expectedList = [
            [
                'class' => QametaLabel::class,
                'name' => 'a',
                'value' => 'b',
            ],
        ];
        self::assertSame($expectedList, $this->exportLabels(...$label->convert()));
    }

    public function testConvert_WithTwoValues_ResultHasSameValues(): void
    {
        $label = $this->getLabelInstance('withTwoValues');
        $expectedList = [
            [
                'class' => QametaLabel::class,
                'name' => 'a',
                'value' => 'b',
            ],
            [
                'class' => QametaLabel::class,
                'name' => 'a',
                'value' => 'c',
            ],
        ];
        self::assertSame($expectedList, $this->exportLabels(...$label->convert()));
    }

    /**
     * @Label("a", values={"b"})
     */
    protected function withSingleValue(): void
    {
    }

    /**
     * @Label("a", values={"b", "c"})
     */
    protected function withTwoValues(): void
    {
    }

    /**
     * @psalm-suppress DeprecatedClass
     */
    public function getLabelInstance(string $methodName): Label
    {
        return $this->getLegacyAttributeInstance(Label::class, $methodName);
    }
}
