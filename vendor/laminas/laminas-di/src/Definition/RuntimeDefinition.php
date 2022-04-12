<?php

declare(strict_types=1);

namespace Laminas\Di\Definition;

use Laminas\Di\Definition\Reflection\ClassDefinition;
use Laminas\Di\Exception;

use function array_keys;
use function array_merge;
use function class_exists;

/**
 * Class definitions based on runtime reflection
 */
class RuntimeDefinition implements DefinitionInterface
{
    /** @var ClassDefinition[] */
    private $definition = [];

    /** @var bool[] */
    private $explicitClasses;

    /**
     * @param null|string[] $explicitClasses
     */
    public function __construct(?array $explicitClasses = null)
    {
        if ($explicitClasses) {
            $this->setExplicitClasses($explicitClasses);
        }
    }

    /**
     * Set explicit class names
     *
     * @see addExplicitClass()
     *
     * @param string[] $explicitClasses An array of class names
     * @throws Exception\ClassNotFoundException
     */
    public function setExplicitClasses(array $explicitClasses): self
    {
        $this->explicitClasses = [];

        foreach ($explicitClasses as $class) {
            $this->addExplicitClass($class);
        }

        return $this;
    }

    /**
     * Add class name explicitly
     *
     * Adding classes this way will cause the defintion to report them when getClasses()
     * is called, even when they're not yet loaded.
     *
     * @throws Exception\ClassNotFoundException
     */
    public function addExplicitClass(string $class): self
    {
        if (! class_exists($class)) {
            throw new Exception\ClassNotFoundException($class);
        }

        if (! $this->explicitClasses) {
            $this->explicitClasses = [];
        }

        $this->explicitClasses[$class] = true;
        return $this;
    }

    /**
     * @param string $class The class name to load
     * @throws Exception\ClassNotFoundException
     */
    private function loadClass(string $class)
    {
        if (! $this->hasClass($class)) {
            throw new Exception\ClassNotFoundException($class);
        }

        $this->definition[$class] = new ClassDefinition($class);
    }

    /**
     * @return string[]
     */
    public function getClasses(): array
    {
        if (! $this->explicitClasses) {
            return array_keys($this->definition);
        }

        return array_keys(array_merge($this->definition, $this->explicitClasses));
    }

    public function hasClass(string $class): bool
    {
        return class_exists($class);
    }

    /**
     * @return ClassDefinition
     * @throws Exception\ClassNotFoundException
     */
    public function getClassDefinition(string $class): ClassDefinitionInterface
    {
        if (! isset($this->definition[$class])) {
            $this->loadClass($class);
        }

        return $this->definition[$class];
    }
}
