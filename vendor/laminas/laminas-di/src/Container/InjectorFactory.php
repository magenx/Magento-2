<?php

declare(strict_types=1);

namespace Laminas\Di\Container;

use Laminas\Di\ConfigInterface;
use Laminas\Di\Injector;
use Laminas\Di\InjectorInterface;
use Psr\Container\ContainerInterface;
use Zend\Di\ConfigInterface as LegacyConfigInterace;

/**
 * Implements the DependencyInjector service factory for laminas-servicemanager
 */
class InjectorFactory
{
    private function createConfig(ContainerInterface $container): ConfigInterface
    {
        if ($container->has(ConfigInterface::class)) {
            return $container->get(ConfigInterface::class);
        }

        if ($container->has(LegacyConfigInterace::class)) {
            return $container->get(LegacyConfigInterace::class);
        }

        return (new ConfigFactory())->create($container);
    }

    /**
     * {@inheritDoc}
     */
    public function create(ContainerInterface $container): InjectorInterface
    {
        $config = $this->createConfig($container);
        return new Injector($config, $container);
    }

    /**
     * Make the instance invokable
     */
    public function __invoke(ContainerInterface $container): InjectorInterface
    {
        return $this->create($container);
    }
}
