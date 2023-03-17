<?php

declare(strict_types=1);

namespace Laminas\Di\Exception;

use ReflectionNamedType;
use ReflectionType;

use function sprintf;

/**
 * This Exception class is intended to be thrown whenever a
 * ReflectionUnionType::class or ReflectionIntersectionType::class
 * is returned to code that can only function correctly with an instance
 * of ReflectionType::class pre PHP 8.0.
 */
final class UnsupportedReflectionTypeException extends RuntimeException
{
    private function __construct(ReflectionType $reflectionType)
    {
        parent::__construct(
            sprintf(
                "Unusable reflection type '%s', object of type '%s' required",
                $reflectionType::class,
                ReflectionNamedType::class
            )
        );
    }

    public static function fromUnionOrIntersectionType(ReflectionType $reflectionType): self
    {
        return new self($reflectionType);
    }
}
