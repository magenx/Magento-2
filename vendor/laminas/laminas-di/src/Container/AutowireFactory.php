<?php

declare(strict_types=1);

namespace Laminas\Di\Container;

use Laminas\Di\Exception;
use Laminas\Di\InjectorInterface;
use Psr\Container\ContainerInterface;

/**
 * Create instances with autowiring
 */
class AutowireFactory
{
    /**
     * Retrieves the injector from a container
     *
     * @param ContainerInterface $container The container context for this factory
     * @throws Exception\RuntimeException When no dependency injector is available.
     */
    private function getInjector(ContainerInterface $container): InjectorInterface
    {
        $injector = $container->get(InjectorInterface::class);

        if (! $injector instanceof InjectorInterface) {
            throw new Exception\RuntimeException(
                'Could not get a dependency injector form the container implementation'
            );
        }

        return $injector;
    }

    /**
     * Check creatability of the requested name
     *
     * @param string $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        if (! $container->has(InjectorInterface::class)) {
            return false;
        }

        /** @psalm-suppress RedundantCastGivenDocblockType Avoid behavior BC break */
        return $this->getInjector($container)->canCreate((string) $requestedName);
    }

    /**
     * Create an instance
     *
     * @template T of object
     * @param string|class-string<T> $requestedName
     * @param array<mixed>|null $options
     * @return T
     */
    public function create(ContainerInterface $container, string $requestedName, ?array $options = null)
    {
        return $this->getInjector($container)->create($requestedName, $options ?: []);
    }

    /**
     * Make invokable and implement the laminas-service factory pattern
     *
     * @template T of object
     * @param string|class-string<T> $requestedName
     * @param array<mixed>|null $options
     * @return T
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        /** @psalm-suppress RedundantCastGivenDocblockType Avoid behavior BC break */
        return $this->create($container, (string) $requestedName, $options);
    }
}
