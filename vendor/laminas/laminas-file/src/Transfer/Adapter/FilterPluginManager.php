<?php

namespace Laminas\File\Transfer\Adapter;

use Interop\Container;
use Laminas\Filter\File;
use Laminas\Filter\FilterPluginManager as BaseManager;
use Laminas\ServiceManager;

/**
 * Plugin manager implementation for the filter chain.
 *
 * Enforces that filters retrieved are instances of
 * FilterInterface. Additionally, it registers a number of default filters.
 *
 * @deprecated since 2.7.0, and scheduled for removal with 3.0.0
 */
class FilterPluginManager extends BaseManager
{
    protected $defaultFileFilterAliases = [
        'decrypt'       => File\Decrypt::class,
        'Decrypt'       => File\Decrypt::class,
        'encrypt'       => File\Encrypt::class,
        'Encrypt'       => File\Encrypt::class,
        'lowercase'     => File\LowerCase::class,
        'lowerCase'     => File\LowerCase::class,
        'LowerCase'     => File\LowerCase::class,
        'rename'        => File\Rename::class,
        'Rename'        => File\Rename::class,
        'uppercase'     => File\UpperCase::class,
        'upperCase'     => File\UpperCase::class,
        'UpperCase'     => File\UpperCase::class,
    ];

    /**
     * Constructor
     *
     * Merges default aliases pertinent to this plugin manager with those
     * defined in the parent filter plugin manager.
     *
     * @param null|ServiceManager\ConfigInterface|Container\ContainerInterface $configOrContainerInstance
     * @param array $v3config If $configOrContainerInstance is a container, this
     *     value will be passed to the parent constructor.
     */
    public function __construct($configOrContainerInstance = null, array $v3config = [])
    {
        $this->aliases = array_merge($this->defaultFileFilterAliases, $this->aliases);
        parent::__construct($configOrContainerInstance, $v3config);
    }
}
