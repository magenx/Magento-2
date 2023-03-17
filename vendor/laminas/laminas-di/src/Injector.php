<?php

declare(strict_types=1);

namespace Laminas\Di;

use Laminas\Di\Definition\DefinitionInterface;
use Laminas\Di\Exception\ClassNotFoundException;
use Laminas\Di\Exception\InvalidCallbackException;
use Laminas\Di\Exception\RuntimeException;
use Laminas\Di\Resolver\DependencyResolverInterface;
use Laminas\Di\Resolver\InjectionInterface;
use Laminas\Di\Resolver\TypeInjection;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function array_pop;
use function class_exists;
use function implode;
use function in_array;
use function sprintf;

/**
 * Dependency injector that can generate instances using class definitions and configured instance parameters
 */
class Injector implements InjectorInterface
{
    /** @var DefinitionInterface */
    protected $definition;

    /** @var ContainerInterface */
    protected $container;

    /** @var DependencyResolverInterface */
    protected $resolver;

    /** @var ConfigInterface */
    protected $config;

    /** @var string[] */
    protected $instantiationStack = [];

    /**
     * Constructor
     *
     * @param ConfigInterface|null             $config A custom configuration to utilize. An empty configuration is used
     *                  when null is passed or the parameter is omitted.
     * @param ContainerInterface|null          $container The IoC container to retrieve dependency instances.
     *               `Laminas\Di\DefaultContainer` is used when null is passed or the parameter is omitted.
     * @param null|DefinitionInterface         $definition A custom definition instance for creating requested
     *               instances. The runtime definition is used when null is passed or the parameter is omitted.
     * @param DependencyResolverInterface|null $resolver A custom resolver instance to resolve dependencies.
     *      The default resolver is used when null is passed or the parameter is omitted
     */
    public function __construct(
        ?ConfigInterface $config = null,
        ?ContainerInterface $container = null,
        ?DefinitionInterface $definition = null,
        ?DependencyResolverInterface $resolver = null
    ) {
        $this->definition = $definition ?: new Definition\RuntimeDefinition();
        $this->config     = $config ?: new Config();
        $this->resolver   = $resolver ?: new Resolver\DependencyResolver($this->definition, $this->config);
        $this->setContainer($container ?: new DefaultContainer($this));
    }

    /**
     * Set the ioc container
     *
     * Sets the ioc container to utilize for fetching instances of dependencies
     *
     * @return $this
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->resolver->setContainer($container);
        $this->container = $container;

        return $this;
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * Returns the class name for the requested type
     */
    private function getClassName(string $type): string
    {
        if ($this->config->isAlias($type)) {
            return $this->config->getClassForAlias($type) ?? $type;
        }

        return $type;
    }

    /**
     * Check if the given type name can be instantiated
     *
     * This will be the case if the name points to a class.
     */
    public function canCreate(string $name): bool
    {
        $class = $this->getClassName($name);
        return class_exists($class);
    }

    /**
     * Create the instance with auto wiring
     *
     * @template T of object
     * @param string|class-string<T> $name Class name or service alias
     * @param array<mixed> $options Constructor parameters, keyed by the parameter name.
     * @return T
     * @throws ClassNotFoundException
     * @throws RuntimeException
     */
    public function create(string $name, array $options = [])
    {
        if (in_array($name, $this->instantiationStack)) {
            throw new Exception\CircularDependencyException(sprintf(
                'Circular dependency: %s -> %s',
                implode(' -> ', $this->instantiationStack),
                $name
            ));
        }

        $this->instantiationStack[] = $name;

        try {
            $instance = $this->createInstance($name, $options);
        } finally {
            array_pop($this->instantiationStack);
        }

        return $instance;
    }

    /**
     * Retrieve a class instance based on the type name
     *
     * Any parameters provided will be used as constructor arguments only.
     *
     * @template T of object
     * @param string|class-string<T> $name The type name to instantiate.
     * @param array<mixed> $params Constructor arguments, keyed by the parameter name.
     * @return T
     * @throws InvalidCallbackException
     * @throws ClassNotFoundException
     */
    protected function createInstance(string $name, array $params)
    {
        $class = $this->getClassName($name);

        if (! $this->definition->hasClass($class)) {
            $aliasMsg = $name !== $class ? ' (specified by alias ' . $name . ')' : '';
            throw new ClassNotFoundException(sprintf(
                'Class %s%s could not be located in provided definitions.',
                $class,
                $aliasMsg
            ));
        }

        if (! class_exists($class)) {
            throw new ClassNotFoundException(sprintf(
                'Class by name %s does not exist',
                $class
            ));
        }

        $callParameters = $this->resolveParameters($name, $params);

        /**
         * @psalm-suppress MixedMethodCall
         * @psalm-var T
         */
        return new $class(...$callParameters);
    }

    /**
     * @return mixed The value to inject into the instance
     */
    private function getInjectionValue(InjectionInterface $injection)
    {
        $container      = $this->container;
        $containerTypes = [
            ContainerInterface::class,
            // Be backwards compatible with interop/container:
            'Interop\Container\ContainerInterface', // phpcs:ignore 
        ];

        if (
            $injection instanceof TypeInjection
            && ! $container->has((string) $injection)
            && in_array((string) $injection, $containerTypes, true)
        ) {
            return $container;
        }

        return $injection->toValue($container);
    }

    /**
     * Resolve parameters
     *
     * At first this method utilizes the resolver to obtain the types to inject.
     * If this was successful (the resolver returned a non-null value), it will use
     * the ioc container to fetch the instances
     *
     * @param string                $type The class or alias name to resolve for
     * @param array<string, mixed>  $params Provided call time parameters
     * @return list<mixed> The resulting arguments in call order
     * @throws Exception\UndefinedReferenceException When a type cannot be
     *     obtained via the ioc container and the method is required for
     *     injection.
     * @throws Exception\CircularDependencyException When a circular dependency is detected.
     */
    private function resolveParameters(string $type, array $params = []): array
    {
        $resolved    = $this->resolver->resolveParameters($type, $params);
        $foundParams = [];

        foreach ($resolved as $injection) {
            try {
                $foundParams[] = $this->getInjectionValue($injection);
            } catch (NotFoundExceptionInterface $containerException) {
                throw new Exception\UndefinedReferenceException(
                    $containerException->getMessage(),
                    (int) $containerException->getCode(),
                    $containerException
                );
            }
        }

        return $foundParams;
    }
}
