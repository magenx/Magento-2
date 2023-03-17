<?php

declare(strict_types=1);

namespace Qameta\Allure\Attribute;

use Doctrine\Common\Annotations\IndexedReader;
use Doctrine\Common\Annotations\Reader;
use Qameta\Allure\Legacy\Annotation\LegacyAnnotationInterface;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionProperty;

use function array_filter;
use function array_values;
use function is_a;
use function is_array;

final class LegacyAttributeReader implements AttributeReaderInterface
{
    private Reader $legacyDelegate;

    public function __construct(
        Reader $legacyReader,
        private AttributeReaderInterface $delegate,
    ) {
        $this->legacyDelegate = new IndexedReader($legacyReader);
    }

    /**
     * @param ReflectionClass   $class
     * @param class-string|null $name
     * @return list<AttributeInterface>
     */
    public function getClassAnnotations(ReflectionClass $class, ?string $name = null): array
    {
        $legacyAnnotations = $this->legacyDelegate->getClassAnnotations($class);

        return [
            ...$this->convertLegacyAnnotations($name, ...array_values($legacyAnnotations)),
            ...$this->delegate->getClassAnnotations($class, $name),
        ];
    }

    /**
     * @param ReflectionMethod  $method
     * @param class-string|null $name
     * @return list<AttributeInterface>
     */
    public function getMethodAnnotations(ReflectionMethod $method, ?string $name = null): array
    {
        $legacyAnnotations = $this->legacyDelegate->getMethodAnnotations($method);

        return [
            ...$this->convertLegacyAnnotations($name, ...array_values($legacyAnnotations)),
            ...$this->delegate->getMethodAnnotations($method, $name),
        ];
    }

    /**
     * @param ReflectionProperty $property
     * @param class-string|null  $name
     * @return list<AttributeInterface>
     */
    public function getPropertyAnnotations(ReflectionProperty $property, ?string $name = null): array
    {
        $legacyAnnotations = $this->legacyDelegate->getPropertyAnnotations($property);

        return [
            ...$this->convertLegacyAnnotations($name, ...array_values($legacyAnnotations)),
            ...$this->delegate->getPropertyAnnotations($property, $name),
        ];
    }

    /**
     * @param ReflectionFunction $function
     * @param class-string|null  $name
     * @return list<AttributeInterface>
     */
    public function getFunctionAnnotations(ReflectionFunction $function, ?string $name = null): array
    {
        return $this->delegate->getFunctionAnnotations($function, $name);
    }

    /**
     * @param class-string|null    $name
     * @param object            ...$annotations
     * @return list<AttributeInterface>
     */
    private function convertLegacyAnnotations(?string $name, object ...$annotations): array
    {
        $result = [];
        foreach ($annotations as $annotation) {
            if (!$annotation instanceof LegacyAnnotationInterface) {
                continue;
            }
            $converted = $annotation->convert();
            if (!is_array($converted)) {
                $converted = [$converted];
            }
            $result = [...$result, ...$converted];
        }

        $filteredResult = array_filter(
            $result,
            fn (AttributeInterface $attribute): bool => !isset($name) || is_a($attribute, $name, true),
        );

        return array_values($filteredResult);
    }

    /**
     * @param array $variables
     * @return list<AttributeInterface>
     */
    public function getEnvironmentAnnotations(array $variables): array
    {
        return [];
    }
}
