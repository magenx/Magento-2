<?php

declare(strict_types=1);

namespace Laminas\Di;

use ArrayAccess;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Stdlib\Parameters;
use Traversable;

use function array_pop;
use function class_exists;
use function interface_exists;
use function is_array;
use function is_iterable;
use function strpos;
use function trigger_error;

use const E_USER_DEPRECATED;

/**
 * Provides a migration config from laminas-di 2.x configuration arrays
 */
class LegacyConfig extends Config
{
    /**
     * @param array|ArrayAccess $config
     */
    public function __construct($config)
    {
        parent::__construct([]);

        if ($config instanceof Traversable) {
            $config = ArrayUtils::iteratorToArray($config);
        }

        if (! is_array($config)) {
            throw new Exception\InvalidArgumentException('Config data must be an array or implement Traversable');
        }

        if (isset($config['instance'])) {
            $this->configureInstance($config['instance']);
        }
    }

    /**
     * @param mixed $parameters
     * @return mixed[]
     */
    private function prepareParametersArray($parameters, string $class)
    {
        $prepared = [];

        foreach ($parameters as $key => $value) {
            if (strpos($key, ':') !== false) {
                trigger_error('Full qualified parameter positions are no longer supported', E_USER_DEPRECATED);
            }

            $prepared[$key] = $value;
        }

        return $prepared;
    }

    /**
     * @param iterable $config
     */
    private function configureInstance($config)
    {
        foreach ($config as $target => $data) {
            switch ($target) {
                case 'aliases':
                case 'alias':
                    foreach ($data as $name => $class) {
                        if (class_exists($class) || interface_exists($class)) {
                            $this->setAlias($name, $class);
                        }
                    }
                    break;

                case 'preferences':
                case 'preference':
                    foreach ($data as $type => $pref) {
                        $preference = is_array($pref) ? array_pop($pref) : $pref;
                        $this->setTypePreference($type, $preference);
                    }
                    break;

                default:
                    $config     = new Parameters($data);
                    $parameters = $config->get('parameters', $config->get('parameter'));

                    if (is_iterable($parameters)) {
                        $parameters = $this->prepareParametersArray($parameters, $target);
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
