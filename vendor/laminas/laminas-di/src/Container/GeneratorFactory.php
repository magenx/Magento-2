<?php

declare(strict_types=1);

namespace Laminas\Di\Container;

use Laminas\Di\CodeGenerator\InjectorGenerator;
use Laminas\Di\ConfigInterface;
use Laminas\Di\Definition\RuntimeDefinition;
use Laminas\Di\Resolver\DependencyResolver;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

use function assert;
use function is_string;

class GeneratorFactory
{
    private function getConfig(ContainerInterface $container): ConfigInterface
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
     * @psalm-suppress MixedAssignment
     * @psalm-suppress MixedArrayAccess
     */
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
            $logger = $container->get((string) $aotConfig['logger']);
            assert($logger instanceof LoggerInterface);
        }

        assert($namespace === null || is_string($namespace));

        $generator = new InjectorGenerator($diConfig, $resolver, $namespace, $logger);

        if (isset($aotConfig['directory'])) {
            $generator->setOutputDirectory((string) $aotConfig['directory']);
        }

        return $generator;
    }

    public function __invoke(ContainerInterface $container): InjectorGenerator
    {
        return $this->create($container);
    }
}
