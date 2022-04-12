<?php

declare(strict_types=1);

namespace Laminas\Di;

use Zend\Di\CodeGenerator\InjectorGenerator as LegacyInjectorGenerator;
use Zend\Di\ConfigInterface as LegacyConfigInterface;
use Zend\Di\InjectorInterface as LegacyInjectorInterfae;

/**
 * Implements the config provider for mezzio
 */
class ConfigProvider
{
    /**
     * Implements the config provider
     *
     * @return array The configuration for mezzio
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
        ];
    }

    /**
     * Returns the dependency (service manager) configuration
     */
    public function getDependencyConfig(): array
    {
        return [
            // Legacy Zend Framework aliases
            'aliases'            => [
                LegacyInjectorInterfae::class  => InjectorInterface::class,
                LegacyConfigInterface::class   => ConfigInterface::class,
                LegacyInjectorGenerator::class => CodeGenerator\InjectorGenerator::class,
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
