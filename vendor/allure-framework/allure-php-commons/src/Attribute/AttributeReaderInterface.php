<?php

declare(strict_types=1);

namespace Qameta\Allure\Attribute;

use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionProperty;

interface AttributeReaderInterface
{
    /**
     * @param ReflectionClass $class
     * @param class-string|null     $name
     * @return list<AttributeInterface>
     */
    public function getClassAnnotations(ReflectionClass $class, ?string $name = null): array;

    /**
     * @param ReflectionMethod $method
     * @param class-string|null      $name
     * @return list<AttributeInterface>
     */
    public function getMethodAnnotations(ReflectionMethod $method, ?string $name = null): array;

    /**
     * @param ReflectionProperty $property
     * @param class-string|null        $name
     * @return list<AttributeInterface>
     */
    public function getPropertyAnnotations(ReflectionProperty $property, ?string $name = null): array;

    /**
     * @param ReflectionFunction $function
     * @param class-string|null        $name
     * @return list<AttributeInterface>
     */
    public function getFunctionAnnotations(ReflectionFunction $function, ?string $name = null): array;

    /**
     * @param array $variables
     * @return list<AttributeInterface>
     */
    public function getEnvironmentAnnotations(array $variables): array;
}
