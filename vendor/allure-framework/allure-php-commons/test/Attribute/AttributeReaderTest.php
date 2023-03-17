<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Attribute;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Attribute\AttributeInterface;
use Qameta\Allure\Attribute\AttributeReader;
use Qameta\Allure\Attribute\Label;
use Qameta\Allure\Attribute\LabelInterface;

use function array_map;
use function array_values;

/**
 * @covers \Qameta\Allure\Attribute\AttributeReader
 */
class AttributeReaderTest extends TestCase
{
    /**
     * @param string $envLabelPrefix
     * @param array  $variables
     * @return void
     * @dataProvider providerVariablesWithoutLabels
     */
    public function testGetEnvironmentAnnotations_VariablesWithoutLabels_ReturnsEmptyList(
        string $envLabelPrefix,
        array $variables,
    ): void {
        $reader = new AttributeReader($envLabelPrefix);
        $attributes = $reader->getEnvironmentAnnotations($variables);
        self::assertEmpty($attributes);
    }

    /**
     * @return iterable<string, array{string, array}>
     */
    public static function providerVariablesWithoutLabels(): iterable
    {
        return [
            'No variables' => ['a', []],
            'Variables with no matching prefix' => ['a', ['b' => 'c', 'd' => 'e']],
            'Variable with full name as prefix' => ['a', ['a' => 'b']],
        ];
    }

    /**
     * @param string      $envLabelPrefix
     * @param array       $variables
     * @param list<array> $expectedData
     * @return void
     * @dataProvider providerVariablesWithLabels
     */
    public function testGetEnvironment_VariablesWithLabels_ReturnMatchingLabels(
        string $envLabelPrefix,
        array $variables,
        array $expectedData,
    ): void {
        $reader = new AttributeReader($envLabelPrefix);
        $attributes = $reader->getEnvironmentAnnotations($variables);
        self::assertSame($expectedData, $this->exportAttributes(...$attributes));
    }

    /**
     * @return iterable<string, array{string, array, list<array>}>
     */
    public static function providerVariablesWithLabels(): iterable
    {
        return [
            'Variable with double prefix' => [
                'a',
                ['aa' => 'b'],
                [['class' => Label::class, 'name' => 'a', 'value' => 'b']],
            ],
            'Variable with non-string name' => [
                '1',
                [12 => 'a'],
                [['class' => Label::class, 'name' => '2', 'value' => 'a']],
            ],
            'Variable with non-string value' => [
                'a',
                ['ab' => 1],
                [['class' => Label::class, 'name' => 'b', 'value' => '1']],
            ],
            'Variable with uppercase name' => [
                'a',
                ['aB' => 1],
                [['class' => Label::class, 'name' => 'B', 'value' => '1']],
            ],
            'First variable with matching prefix' => [
                'a',
                ['ab' => 'c', 'd' => 'e'],
                [['class' => Label::class, 'name' => 'b', 'value' => 'c']],
            ],
            'Second variable with matching prefix' => [
                'a',
                ['b' => 'c', 'ad' => 'e'],
                [['class' => Label::class, 'name' => 'd', 'value' => 'e']],
            ],
            'Two variables with matching prefix' => [
                'a',
                ['ab' => 'c', 'ad' => 'e'],
                [
                    ['class' => Label::class, 'name' => 'b', 'value' => 'c'],
                    ['class' => Label::class, 'name' => 'd', 'value' => 'e'],
                ],
            ],
        ];
    }

    /**
     * @param AttributeInterface ...$attributes
     * @return list<array>
     */
    private function exportAttributes(AttributeInterface ...$attributes): array
    {
        return array_map([$this, 'exportAttribute'], array_values($attributes));
    }

    /**
     * @param AttributeInterface $attribute
     * @return array<string, mixed>
     */
    private function exportAttribute(AttributeInterface $attribute): array
    {
        $data = ['class' => $attribute::class];
        if ($attribute instanceof LabelInterface) {
            $data['name'] = $attribute->getName();
            $data['value'] = $attribute->getValue();
        }

        return $data;
    }
}
