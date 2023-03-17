<?php

declare(strict_types=1);

namespace Laminas\Di\CodeGenerator;

use Psr\Container\ContainerInterface;

/**
 * @template T extends object
 */
interface FactoryInterface
{
    /**
     * Create an instance
     *
     * @param array<mixed> $options
     * @return T
     */
    public function create(ContainerInterface $container, array $options);
}
