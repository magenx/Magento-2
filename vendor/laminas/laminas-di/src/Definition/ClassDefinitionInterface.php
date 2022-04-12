<?php

declare(strict_types=1);

namespace Laminas\Di\Definition;

use ReflectionClass;

interface ClassDefinitionInterface
{
    public function getReflection(): ReflectionClass;

    /**
     * @return string[]
     */
    public function getSupertypes(): array;

    /**
     * @return string[]
     */
    public function getInterfaces(): array;

    /**
     * @return ParameterInterface[]
     */
    public function getParameters(): array;
}
