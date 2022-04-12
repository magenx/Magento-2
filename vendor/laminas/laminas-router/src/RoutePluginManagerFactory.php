<?php

declare(strict_types=1);

namespace Laminas\Router;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/** @psalm-suppress DeprecatedInterface */
class RoutePluginManagerFactory implements FactoryInterface
{
    /**
     * Create and return a route plugin manager.
     *
     * @param  string $name
     * @param  null|array $options
     * @return RoutePluginManager
     */
    public function __invoke(ContainerInterface $container, $name, ?array $options = null)
    {
        $options = $options ?: [];
        return new RoutePluginManager($container, $options);
    }

    /**
     * Create and return RoutePluginManager instance.
     *
     * For use with laminas-servicemanager v2; proxies to __invoke().
     *
     * @return RoutePluginManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, RoutePluginManager::class);
    }
}
