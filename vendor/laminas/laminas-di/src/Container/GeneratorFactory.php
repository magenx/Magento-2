<?php

declare(strict_types=1);

namespace Laminas\Di\Container;

use Laminas\Di\CodeGenerator\InjectorGenerator;
use Laminas\Di\ConfigInterface;
use Laminas\Di\Definition\RuntimeDefinition;
use Laminas\Di\Resolver\DependencyResolver;
use Psr\Container\ContainerInterface;
use Zend\Di\ConfigInterface as LegacyConfigInterace;

class GeneratorFactory
{
    private function getConfig(ContainerInterface $container): ConfigInterface
    {
        if ($container->has(ConfigInterface::class)) {
            return $container->get(ConfigInterface::class);
        }

        if ($container->has(LegacyConfigInterace::class)) {
            return $container->get(LegacyConfigInterace::class);
        }

        return (new ConfigFactory())->create($container);
    }

    public function create(ContainerInterface $container): InjectorGenerator
    {
        $diConfig = $this->getConfig($container);
        $resolver = new DependencyResolver(new RuntimeDefinition(), $diConfig);
        $resolver->setContainer($container);

        $config    = $container->has('config') ? $container->get('config') : [];
        $aotConfig = $config['dependencies']['auto']['aot'] ?? [];
        $namespace = $aotConfig['namespace'] ?? null;
        $logger    = null;

        if (isset($aotConfig['logger'])) {
            $logger = $container->get($aotConfig['logger']);
        }

        $generator = new InjectorGenerator($diConfig, $resolver, $namespace, $logger);

        if (isset($aotConfig['directory'])) {
            $generator->setOutputDirectory($aotConfig['directory']);
        }

        return $generator;
    }

    public function __invoke(ContainerInterface $container): InjectorGenerator
    {
        return $this->create($container);
    }
}
