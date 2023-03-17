<?php

declare(strict_types=1);

namespace Qameta\Allure\Attribute;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionProperty;

use function array_filter;
use function array_map;
use function array_values;
use function class_exists;
use function is_a;
use function str_starts_with;
use function strlen;
use function substr;

final class AttributeReader implements AttributeReaderInterface
{
    private const ENV_LABEL_PREFIX = 'ALLURE_LABEL_';

    public function __construct(
        private string $envLabelPrefix = self::ENV_LABEL_PREFIX,
    ) {
    }

    /**
     * @param ReflectionClass $class
     * @param class-string|null     $name
     * @return list<AttributeInterface>
     */
    public function getClassAnnotations(ReflectionClass $class, ?string $name = null): array
    {
        return $this->getAttributeInstances(...$class->getAttributes($name));
    }

    /**
     * @param ReflectionMethod $method
     * @param class-string|null      $name
     * @return list<AttributeInterface>
     */
    public function getMethodAnnotations(ReflectionMethod $method, ?string $name = null): array
    {
        return $this->getAttributeInstances(...$method->getAttributes($name));
    }

    /**
     * @param ReflectionProperty $property
     * @param class-string|null        $name
     * @return list<AttributeInterface>
     */
    public function getPropertyAnnotations(ReflectionProperty $property, ?string $name = null): array
    {
        return $this->getAttributeInstances(...$property->getAttributes($name));
    }

    /**
     * @param ReflectionFunction $function
     * @param class-string|null        $name
     * @return list<AttributeInterface>
     */
    public function getFunctionAnnotations(ReflectionFunction $function, ?string $name = null): array
    {
        return $this->getAttributeInstances(...$function->getAttributes($name));
    }

    /**
     * @param ReflectionAttribute ...$attributes
     * @return list<AttributeInterface>
     */
    private function getAttributeInstances(ReflectionAttribute ...$attributes): array
    {
        /** @psalm-var array<ReflectionAttribute<AttributeInterface>> $filteredAttributes */
        $filteredAttributes = array_filter(
            $attributes,
            fn (ReflectionAttribute $attribute): bool =>
                class_exists($attribute->getName()) &&
                is_a($attribute->getName(), AttributeInterface::class, true),
        );

        return array_map(
            fn (ReflectionAttribute $attribute): AttributeInterface => $attribute->newInstance(),
            array_values($filteredAttributes),
        );
    }

    /**
     * @param array $variables
     * @return list<AttributeInterface>
     */
    public function getEnvironmentAnnotations(array $variables): array
    {
        $labels = [];
        /** @psalm-var mixed $value */
        foreach ($variables as $variableName => $value) {
            if (str_starts_with((string) $variableName, $this->envLabelPrefix)) {
                $labelName = substr((string) $variableName, strlen($this->envLabelPrefix));
                if ('' == $labelName) {
                    continue;
                }
                $labels[] = new Label($labelName, isset($value) ? (string) $value : null);
            }
        }

        return $labels;
    }
}
