<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Legacy\Annotation;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Attribute\Label as QametaLabel;
use Qameta\Allure\Test\Attribute\AnnotationTestTrait;
use Yandex\Allure\Adapter\Annotation\Label;
use Yandex\Allure\Adapter\Annotation\Labels;

use function array_map;

/**
 * @covers \Yandex\Allure\Adapter\Annotation\Labels
 */
class LabelsTest extends TestCase
{
    use AnnotationTestTrait;

    public function testLabels_WithSingleLabel_ReturnsMatchingList(): void
    {
        $labels = $this->getLabelsInstance('demoWithSingleLabel');
        $expectedLabels = [
            ['name' => 'a', 'values' => ['b']],
        ];
        self::assertSame($expectedLabels, $this->exportLegacyLabels(...$labels->labels));
    }

    public function testLabels_WithTwoLabels_ReturnsMatchingList(): void
    {
        $labels = $this->getLabelsInstance('demoWithTwoLabels');
        $expectedLabels = [
            ['name' => 'a', 'values' => ['b']],
            ['name' => 'c', 'values' => ['d', 'e']],
        ];
        self::assertSame($expectedLabels, $this->exportLegacyLabels(...$labels->labels));
    }

    public function testConvert_WithSingleLabel_ReturnsMatchingList(): void
    {
        $labels = $this->getLabelsInstance('demoWithSingleLabel');
        $expectedLabels = [
            [
                'class' => QametaLabel::class,
                'name' => 'a',
                'value' => 'b',
            ],
        ];
        self::assertSame($expectedLabels, $this->exportLabels(...$labels->convert()));
    }

    public function testConvert_WithTwoLabels_ReturnsMatchingList(): void
    {
        $labels = $this->getLabelsInstance('demoWithTwoLabels');
        $expectedLabels = [
            [
                'class' => QametaLabel::class,
                'name' => 'a',
                'value' => 'b',
            ],
            [
                'class' => QametaLabel::class,
                'name' => 'c',
                'value' => 'd',
            ],
            [
                'class' => QametaLabel::class,
                'name' => 'c',
                'value' => 'e',
            ],
        ];
        self::assertSame($expectedLabels, $this->exportLabels(...$labels->convert()));
    }

    /**
     * @Labels(
     *     @Label("a", values={"b"})
     * )
     */
    protected function demoWithSingleLabel(): void
    {
    }

    /**
     * @Labels(
     *     @Label("a", values={"b"}),
     *     @Label("c", values={"d", "e"}),
     * )
     */
    protected function demoWithTwoLabels(): void
    {
    }

    /**
     * @psalm-suppress DeprecatedClass
     */
    private function exportLegacyLabel(Label $label): array
    {
        return [
            'name' => $label->name,
            'values' => $label->values,
        ];
    }

    /**
     * @psalm-suppress DeprecatedClass
     */
    private function exportLegacyLabels(Label ...$labels): array
    {
        return array_map(
            [$this, 'exportLegacyLabel'],
            $labels,
        );
    }

    /**
     * @psalm-suppress DeprecatedClass
     */
    private function getLabelsInstance(string $methodName): Labels
    {
        return $this->getLegacyAttributeInstance(Labels::class, $methodName);
    }
}
