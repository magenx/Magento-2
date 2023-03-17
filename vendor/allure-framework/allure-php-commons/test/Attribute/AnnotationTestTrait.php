<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Attribute;

use Doctrine\Common\Annotations\AnnotationReader;
use Qameta\Allure\Attribute\LabelInterface;
use Qameta\Allure\Attribute\LinkInterface;
use ReflectionMethod;
use RuntimeException;
use Throwable;

use function array_map;

trait AnnotationTestTrait
{
    /**
     * @template T
     * @param class-string<T> $attributeClass
     * @param string $demoMethodName
     * @return T
     */
    protected function getLegacyAttributeInstance(string $attributeClass, string $demoMethodName): object
    {
        try {
            $method = new ReflectionMethod(self::class, $demoMethodName);
        } catch (Throwable $e) {
            throw new RuntimeException("Attribute {$attributeClass} not found in {$demoMethodName}", 0, $e);
        }

        $reader = new AnnotationReader();
        $instance = $reader->getMethodAnnotation($method, $attributeClass);

        return $instance instanceof $attributeClass
            ? $instance
            : throw new RuntimeException("Attribute is not {$attributeClass} instance");
    }

    /**
     * @template T
     * @param class-string<T> $attributeClass
     * @param string $demoMethodName
     * @return T
     */
    protected function getAttributeInstance(string $attributeClass, string $demoMethodName): object
    {
        try {
            $method = new ReflectionMethod(self::class, $demoMethodName);
        } catch (Throwable $e) {
            throw new RuntimeException("Attribute {$attributeClass} not found in {$demoMethodName}", 0, $e);
        }
        $attribute = $method->getAttributes($attributeClass)[0] ?? null;

        return isset($attribute)
            ? $attribute->newInstance()
            : throw new RuntimeException("Attribute {$attributeClass} not found in {$demoMethodName}");
    }

    protected function exportLabel(LabelInterface $label): array
    {
        return [
            'class' => $label::class,
            'name' => $label->getName(),
            'value' => $label->getValue(),
        ];
    }

    protected function exportLabels(LabelInterface ...$labels): array
    {
        return array_map(
            fn (LabelInterface $label) => $this->exportLabel($label),
            $labels,
        );
    }

    protected function exportLink(LinkInterface $link): array
    {
        return [
            'class' => $link::class,
            'type' => $link->getType(),
            'value' => $link->getName(),
        ];
    }

    protected function exportLinks(LinkInterface ...$links): array
    {
        return array_map(
            fn (LinkInterface $link) => $this->exportLink($link),
            $links,
        );
    }
}
