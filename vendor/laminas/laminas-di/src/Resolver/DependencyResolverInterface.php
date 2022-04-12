<?php

declare(strict_types=1);

namespace Laminas\Di\Resolver;

use Laminas\Di\Exception\MissingPropertyException;
use Psr\Container\ContainerInterface;

/**
 * Interface for implementing dependency resolvers
 *
 * The dependency resolver is used by the dependency injector or the
 * code generator to gather the types and values to inject
 */
interface DependencyResolverInterface
{
    /**
     * Set the ioc container
     *
     * @param ContainerInterface $container The ioc container to utilize for
     *     checking for instances
     * @return self Should provide a fluent interface
     */
    public function setContainer(ContainerInterface $container);

    /**
     * Resolve a type prefernece
     *
     * @param string      $type The type/class name of the dependency to resolve the
     *          preference for
     * @param null|string $context The typename of the instance that is created or
     *     in which the dependency should be injected
     * @return null|string Returns the preferred type name or null if there is no
     *     preference
     */
    public function resolvePreference(string $type, ?string $context = null): ?string;

    /**
     * Resolves all parameters for injection
     *
     * @param string $class The class name to resolve the parameters for
     * @param array  $parameters Parameters to use as provided.
     * @return InjectionInterface[] Returns the injection parameters as indexed array. This
     *     array contains either TypeInjection or ValueInjection instances
     * @throws MissingPropertyException  When a parameter could not be resolved.
     */
    public function resolveParameters(string $class, array $parameters = []): array;
}
