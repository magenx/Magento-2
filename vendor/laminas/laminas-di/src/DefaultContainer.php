<?php

declare(strict_types=1);

namespace Laminas\Di;

use Psr\Container\ContainerInterface;

/**
 * Default IoC container implementation.
 *
 * This is using the dependency injector to create instances.
 */
class DefaultContainer implements ContainerInterface
{
    /**
     * Dependency injector
     *
     * @var InjectorInterface
     */
    protected $injector;

    /**
     * Registered services and cached values
     *
     * @var array<string, object>
     */
    protected $services = [];

    public function __construct(InjectorInterface $injector)
    {
        $this->injector = $injector;

        $this->services[InjectorInterface::class]  = $injector;
        $this->services[ContainerInterface::class] = $this;
        $this->services[$injector::class]          = $injector;
        $this->services[static::class]             = $this;
    }

    /**
     * Explicitly set a service
     *
     * @param string $name The name of the service retrievable by get()
     * @param object $service The service instance
     */
    public function setInstance(string $name, $service): self
    {
        $this->services[$name] = $service;
        return $this;
    }

    /**
     * Check if a service is available
     *
     * @see ContainerInterface::has()
     *
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        if (isset($this->services[$name])) {
            return true;
        }

        return $this->injector->canCreate($name);
    }

    /**
     * Retrieve a service
     *
     * Tests first if a service is registered, and, if so,
     * returns it.
     *
     * If the service is not yet registered, it is attempted to be created via
     * the dependency injector and then it is stored for further use.
     *
     * @see ContainerInterface::get()
     *
     * @param string $name
     * @return object
     */
    public function get($name)
    {
        if (! isset($this->services[$name])) {
            $this->services[$name] = $this->injector->create($name);
        }

        return $this->services[$name];
    }
}
