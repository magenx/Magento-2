<?php

declare(strict_types=1);

namespace Laminas\Di\CodeGenerator;

use Psr\Container\ContainerInterface;

interface FactoryInterface
{
    /**
     * Create an instance
     *
     * @return object
     */
    public function create(ContainerInterface $container, array $options);
}
