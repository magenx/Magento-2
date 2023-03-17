<?php

declare(strict_types=1);

namespace Laminas\Di\Definition\Reflection;

use Laminas\Di\Definition\ClassDefinitionInterface;
use ReflectionClass;

class ClassDefinition implements ClassDefinitionInterface
{
    private ReflectionClass $reflection;

    /** @var array<string, Parameter>|null */
    private ?array $parameters = null;

    /** @var list<class-string>|null */
    private ?array $supertypes = null;

    /**
     * @param class-string|ReflectionClass $class
     */
    public function __construct($class)
    {
        if (! $class instanceof ReflectionClass) {
            $class = new ReflectionClass($class);
        }

        $this->reflection = $class;
    }

    /**
     * @psalm-assert list<string> $this->supertypes
     */
    private function reflectSupertypes(): void
    {
        $this->supertypes = [];
        $class            = $this->reflection;

        while ($class = $class->getParentClass()) {
            $this->supertypes[] = $class->name;
        }
    }

    public function getReflection(): ReflectionClass
    {
        return $this->reflection;
    }

    /**
     * @return list<class-string>
     */
    public function getSupertypes(): array
    {
        if ($this->supertypes === null) {
            $this->reflectSupertypes();
        }

        return $this->supertypes;
    }

    /**
     * @return string[]
     */
    public function getInterfaces(): array
    {
        return $this->reflection->getInterfaceNames();
    }

    /**
     * @psalm-assert array<string, Parameter> $this->parameters
     */
    private function reflectParameters(): void
    {
        $this->parameters = [];

        $constructor = $this->reflection->getConstructor();

        if ($constructor === null) {
            return;
        }

        foreach ($constructor->getParameters() as $parameterReflection) {
            $parameter                               = new Parameter($parameterReflection);
            $this->parameters[$parameter->getName()] = $parameter;
        }
    }

    /**
     * @return array<string, Parameter>
     */
    public function getParameters(): array
    {
        if ($this->parameters === null) {
            $this->reflectParameters();
        }

        return $this->parameters;
    }
}
