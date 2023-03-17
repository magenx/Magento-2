<?php

declare(strict_types=1);

namespace Laminas\Di\Container;

use ArrayAccess;
use Laminas\Di\Config;
use Laminas\Di\ConfigInterface;
use Laminas\Di\LegacyConfig;
use Psr\Container\ContainerInterface;

use function array_merge_recursive;
use function assert;
use function is_array;
use function is_iterable;
use function trigger_error;

use const E_USER_DEPRECATED;

/**
 * Factory implementation for creating the definition list
 */
class ConfigFactory
{
    /**
     * @psalm-suppress MixedArrayAccess
     * @return Config
     */
    public function create(ContainerInterface $container): ConfigInterface
    {
        /** @var mixed $config */
        $config = $container->has('config') ? $container->get('config') : [];

        /** @var mixed $data */
        $data = $config['dependencies']['auto'] ?? [];

        /** @var mixed $legacyData */
        $legacyData = $config['di'] ?? null;

        assert(is_array($data));

        if ($legacyData !== null) {
            trigger_error(
                'Detected legacy DI configuration, please upgrade to v3. '
                . 'See https://docs.laminas.dev/laminas-di/migration/ for details.',
                E_USER_DEPRECATED
            );

            assert(is_iterable($legacyData) || $legacyData instanceof ArrayAccess);

            $legacyConfig = new LegacyConfig($legacyData);
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
