<?php

declare(strict_types=1);

namespace Qameta\Allure\Model;

use JsonSerializable;

use function array_filter;
use function array_keys;
use function array_map;
use function get_object_vars;
use function in_array;
use function is_array;

use const ARRAY_FILTER_USE_KEY;

/**
 * This trait implements {@see JsonSerializable::jsonSerialize()} method and can be used to serialize model objects
 * before writing them to output directory.
 */
trait JsonSerializableTrait
{
    /**
     * Returns JSON object that has all properties (except for those listed by {@see excludeFromSerialization()})
     * with {@see prepareForSerialization()} results as values.
     *
     * @return array
     * @see JsonSerializable
     */
    final public function jsonSerialize(): array
    {
        $includedProperties = array_filter(
            get_object_vars($this),
            fn (string $key): bool => !in_array($key, $this->excludeFromSerialization(), true),
            ARRAY_FILTER_USE_KEY,
        );

        $propertyKeys = array_keys($includedProperties);
        $preparedProperties = array_map(
            fn (string $propertyName, mixed $property): mixed => $this->prepareForSerialization(
                $propertyName,
                $property,
            ),
            $propertyKeys,
            $includedProperties,
        );

        return array_map(
            static fn (mixed $property): mixed => is_array($property)
                ? array_filter(
                    $property,
                    static fn(mixed $propertyItem): bool =>
                        !$propertyItem instanceof ResultInterface ||
                        !$propertyItem->getExcluded(),
                )
                : $property,
            array_combine($propertyKeys, $preparedProperties),
        );
    }

    /**
     * Put property name to this list to exclude it from serialization.
     *
     * @return list<string>
     */
    protected function excludeFromSerialization(): array
    {
        return [];
    }

    /**
     * Override this method to replace serialized value for specific properties.
     *
     * @noinspection PhpUnusedParameterInspection
     */
    protected function prepareForSerialization(string $propertyName, mixed $property): mixed
    {
        return $property;
    }
}
