<?php

declare(strict_types=1);

namespace Laminas\Di;

use ArrayAccess;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Stdlib\Parameters;
use Traversable;

use function array_pop;
use function assert;
use function class_exists;
use function is_array;
use function is_iterable;
use function is_string;
use function str_contains;
use function trigger_error;

use const E_USER_DEPRECATED;

/**
 * Provides a migration config from laminas-di 2.x configuration arrays
 */
class LegacyConfig extends Config
{
    /**
     * @param iterable<mixed>|ArrayAccess<mixed, mixed> $config
     */
    public function __construct($config)
    {
        parent::__construct([]);

        if ($config instanceof Traversable) {
            $config = ArrayUtils::iteratorToArray($config);
        }

        /** @psalm-suppress DocblockTypeContradiction Can this whole typecheck statement be dropped? */
        if (! is_array($config) && ! $config instanceof ArrayAccess) {
            throw new Exception\InvalidArgumentException('Config data must be an array or implement Traversable');
        }

        if (isset($config['instance']) && is_iterable($config['instance'])) {
            $this->configureInstance($config['instance']);
        }
    }

    /**
     * @psalm-suppress MixedAssignment
     * @param iterable<mixed> $parameters
     * @return array<array-key, mixed>
     */
    private function prepareParametersArray($parameters): array
    {
        $prepared = [];

        foreach ($parameters as $key => $value) {
            $key = (string) $key;

            if (str_contains($key, ':')) {
                trigger_error('Full qualified parameter positions are no longer supported', E_USER_DEPRECATED);
            }

            $prepared[$key] = $value;
        }

        return $prepared;
    }

    /**
     * @psalm-suppress MixedAssignment
     * @param iterable<mixed> $config
     */
    private function configureInstance($config): void
    {
        /** @var mixed $data*/
        foreach ($config as $target => $data) {
            switch ($target) {
                case 'aliases':
                case 'alias':
                    assert(is_iterable($data));

                    foreach ($data as $name => $class) {
                        if (is_string($class) && class_exists($class)) {
                            $this->setAlias((string) $name, $class);
                        }
                    }

                    break;

                case 'preferences':
                case 'preference':
                    assert(is_iterable($data));

                    foreach ($data as $type => $pref) {
                        $preference = is_array($pref) ? array_pop($pref) : $pref;
                        $this->setTypePreference((string) $type, (string) $preference);
                    }

                    break;

                default:
                    assert(is_string($target));

                    $config     = new Parameters(is_array($data) ? $data : []);
                    $parameters = $config->get('parameters', $config->get('parameter'));

                    if (is_iterable($parameters)) {
                        $parameters = $this->prepareParametersArray($parameters);
                        $this->setParameters($target, $parameters);
                    }

                    break;
            }
        }
    }

    /**
     * Export the configuraton to an array
     */
    public function toArray(): array
    {
        return [
            'preferences' => $this->preferences,
            'types'       => $this->types,
        ];
    }
}
