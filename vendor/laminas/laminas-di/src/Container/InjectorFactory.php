<?php

declare(strict_types=1);

namespace Laminas\Di\Container;

use Laminas\Di\ConfigInterface;
use Laminas\Di\Injector;
use Laminas\Di\InjectorInterface;
use Psr\Container\ContainerInterface;

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

        if ($container->has('Zend\Di\ConfigInterface')) {
            /** @psalm-var ConfigInterface */
            return $container->get('Zend\Di\ConfigInterface');
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
