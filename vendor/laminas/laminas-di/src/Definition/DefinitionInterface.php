<?php

declare(strict_types=1);

namespace Laminas\Di\Definition;

use Laminas\Di\Exception\ClassNotFoundException;

/**
 * Interface for class definitions
 */
interface DefinitionInterface
{
    /**
     * All class names in this definition
     *
     * @return string[]
     */
    public function getClasses(): array;

    /**
     * Whether a class exists in this definition
     */
    public function hasClass(string $class): bool;

    /**
     * @throws ClassNotFoundException
     */
    public function getClassDefinition(string $class): ClassDefinitionInterface;
}
