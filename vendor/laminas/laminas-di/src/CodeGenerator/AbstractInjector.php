<?php

declare(strict_types=1);

namespace Laminas\Di\CodeGenerator;

use Laminas\Di\DefaultContainer;
use Laminas\Di\InjectorInterface;
use Psr\Container\ContainerInterface;

/**
 * Abstract class for code generated dependency injectors
 */
abstract class AbstractInjector implements InjectorInterface
{
    /** @var array<string, class-string|FactoryInterface> */
    protected $factories = [];

    /** @var array<string, FactoryInterface> */
    private array $factoryInstances = [];

    private ContainerInterface $container;

    private InjectorInterface $injector;

    public function __construct(InjectorInterface $injector, ?ContainerInterface $container = null)
    {
        $this->injector  = $injector;
        $this->container = $container ?: new DefaultContainer($this);

        $this->loadFactoryList();
    }

    /**
     * Init factory list
     */
    abstract protected function loadFactoryList(): void;

    private function setFactory(string $type, FactoryInterface $factory): void
    {
        $this->factoryInstances[$type] = $factory;
    }

    private function getFactory(string $type): FactoryInterface
    {
        if (isset($this->factoryInstances[$type])) {
            return $this->factoryInstances[$type];
        }

        $factoryClass = $this->factories[$type];
        $factory      = $factoryClass instanceof FactoryInterface ? $factoryClass : new $factoryClass();

        $this->setFactory($type, $factory);

        return $factory;
    }

    public function canCreate(string $name): bool
    {
        return $this->hasFactory($name) || $this->injector->canCreate($name);
    }

    private function hasFactory(string $name): bool
    {
        return isset($this->factories[$name]);
    }

    /** @return object */
    public function create(string $name, array $options = [])
    {
        if ($this->hasFactory($name)) {
            return $this->getFactory($name)->create($this->container, $options);
        }

        return $this->injector->create($name, $options);
    }
}
