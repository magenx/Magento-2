<?php

declare(strict_types=1);

namespace Laminas\Di;

/**
 * Implements the config provider for mezzio
 *
 * @psalm-type DependencyConfigArray = array{
 *  aliases: array<string, string>,
 *  factories: array<string, callable|class-string>,
 *  abstract_factories: list<callable|class-string>
 * }
 */
class ConfigProvider
{
    /**
     * Implements the config provider
     *
     * @return array{dependencies: DependencyConfigArray} The configuration for mezzio
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
        ];
    }

    /**
     * Returns the dependency (service manager) configuration
     *
     * @return DependencyConfigArray
     */
    public function getDependencyConfig(): array
    {
        return [
            // Legacy Zend Framework aliases
            'aliases'            => [
                'Zend\Di\InjectorInterface'               => InjectorInterface::class,
                'Zend\Di\ConfigInterface'                 => ConfigInterface::class,
                'Zend\Di\CodeGenerator\InjectorGenerator' => CodeGenerator\InjectorGenerator::class,
            ],
            'factories'          => [
                InjectorInterface::class               => Container\InjectorFactory::class,
                ConfigInterface::class                 => Container\ConfigFactory::class,
                CodeGenerator\InjectorGenerator::class => Container\GeneratorFactory::class,
            ],
            'abstract_factories' => [
                Container\ServiceManager\AutowireFactory::class,
            ],
        ];
    }
}
