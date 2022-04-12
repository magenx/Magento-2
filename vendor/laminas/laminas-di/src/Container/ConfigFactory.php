<?php

declare(strict_types=1);

namespace Laminas\Di\Container;

use Laminas\Di\Config;
use Laminas\Di\ConfigInterface;
use Laminas\Di\LegacyConfig;
use Psr\Container\ContainerInterface;

use function array_merge_recursive;
use function trigger_error;

use const E_USER_DEPRECATED;

/**
 * Factory implementation for creating the definition list
 */
class ConfigFactory
{
    /**
     * @return Config
     */
    public function create(ContainerInterface $container): ConfigInterface
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $data   = $config['dependencies']['auto'] ?? [];

        if (isset($config['di'])) {
            trigger_error(
                'Detected legacy DI configuration, please upgrade to v3. '
                . 'See https://docs.laminas.dev/laminas-di/migration/ for details.',
                E_USER_DEPRECATED
            );

            $legacyConfig = new LegacyConfig($config['di']);
            $data         = array_merge_recursive($legacyConfig->toArray(), $data);
        }

        return new Config($data);
    }

    /**
     * Make the instance invokable
     */
    public function __invoke(ContainerInterface $container): ConfigInterface
    {
        return $this->create($container);
    }
}
