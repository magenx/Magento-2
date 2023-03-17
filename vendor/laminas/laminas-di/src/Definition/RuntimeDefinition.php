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
    /** @var array<class-string, ClassDefinition> */
    private array $definition = [];

    /** @var array<class-string, bool> */
    private array $explicitClasses;

    /**
     * @param null|class-string[] $explicitClasses
     */
    public function __construct(?array $explicitClasses = null)
    {
        $this->explicitClasses = [];
        $this->setExplicitClasses($explicitClasses ?? []);
    }

    /**
     * Set explicit class names
     *
     * @see addExplicitClass()
     *
     * @param class-string[] $explicitClasses An array of class names
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
     * @param class-string $class
     * @throws Exception\ClassNotFoundException
     */
    public function addExplicitClass(string $class): self
    {
        $this->ensureClassExists($class);
        $this->explicitClasses[$class] = true;
        return $this;
    }

    /**
     * @psalm-assert class-string $class
     * @throws Exception\ClassNotFoundException
     */
    private function ensureClassExists(string $class): void
    {
        if (! $this->hasClass($class)) {
            throw new Exception\ClassNotFoundException($class);
        }
    }

    /**
     * @param class-string $class The class name to load
     * @throws Exception\ClassNotFoundException
     */
    private function loadClass(string $class): void
    {
        $this->ensureClassExists($class);

        $this->definition[$class] = new ClassDefinition($class);
    }

    /** @return list<class-string> */
    public function getClasses(): array
    {
        return array_keys(array_merge($this->definition, $this->explicitClasses));
    }

    /**
     * @psalm-assert-if-true class-string $class
     */
    public function hasClass(string $class): bool
    {
        return class_exists($class);
    }

    /** @throws Exception\ClassNotFoundException */
    public function getClassDefinition(string $class): ClassDefinitionInterface
    {
        if (! isset($this->definition[$class])) {
            $this->loadClass($class);
        }

        return $this->definition[$class];
    }
}
