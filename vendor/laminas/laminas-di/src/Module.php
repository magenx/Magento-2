<?php

declare(strict_types=1);

namespace Laminas\Di;

/**
 * Provides Module functionality for Laminas applications
 *
 * To add the DI integration to your application use laminas frameworks component installer or
 * add `Laminas\\Di` to the Laminas modules list:
 *
 * <code>
 *  // application.config.php
 *  return [
 *      // ...
 *      'modules' => [
 *          'Laminas\\Di',
 *          // ...
 *      ]
 *  ];
 * </code>
 *
 * @psalm-import-type DependencyConfigArray from ConfigProvider
 */
class Module
{
    /**
     * Returns the configuration for laminas-mvc
     *
     * @return array{service_manager: DependencyConfigArray}
     */
    public function getConfig(): array
    {
        $provider = new ConfigProvider();
        return [
            'service_manager' => $provider->getDependencyConfig(),
        ];
    }
}
