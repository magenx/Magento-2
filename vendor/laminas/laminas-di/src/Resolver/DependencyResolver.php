<?php

declare(strict_types=1);

namespace Laminas\Di\Resolver;

use Laminas\Di\ConfigInterface;
use Laminas\Di\Definition\ClassDefinitionInterface;
use Laminas\Di\Definition\DefinitionInterface;
use Laminas\Di\Exception;
use Psr\Container\ContainerInterface;
use ReflectionClass;

use function array_filter;
use function array_merge;
use function assert;
use function class_exists;
use function gettype;
use function in_array;
use function interface_exists;
use function is_callable;
use function is_iterable;
use function is_numeric;
use function is_string;
use function sprintf;

/**
 * The default resolver implementation
 */
class DependencyResolver implements DependencyResolverInterface
{
    /** @var ConfigInterface */
    protected $config;

    /** @var DefinitionInterface */
    protected $definition;

    /** @var ContainerInterface|null */
    protected $container;

    /** @var string[] */
    private array $builtinTypes = [
        'string',
        'int',
        'bool',
        'float',
        'double',
        'array',
        'resource',
        'callable',
        'iterable',
    ];

    /** @var array<string, string> */
    private array $gettypeMap = [
        'boolean' => 'bool',
        'integer' => 'int',
        'double'  => 'float',
    ];

    public function __construct(DefinitionInterface $definition, ConfigInterface $config)
    {
        $this->definition = $definition;
        $this->config     = $config;
    }

    private function getClassDefinition(string $type): ClassDefinitionInterface
    {
        if ($this->config->isAlias($type)) {
            $type = $this->config->getClassForAlias($type) ?? $type;
        }

        return $this->definition->getClassDefinition($type);
    }

    /**
     * Returns the configured injections for the requested type
     *
     * If type is an alias it will try to fall back to the class configuration if no parameters
     * were defined for it
     *
     * @param string $requestedType The type name to get injections for
     * @return array Injections for the method indexed by the parameter name
     */
    private function getConfiguredParameters(string $requestedType): array
    {
        $config  = $this->config;
        $params  = $config->getParameters($requestedType);
        $isAlias = $config->isAlias($requestedType);
        $class   = $isAlias
            ? $config->getClassForAlias($requestedType) ?? $requestedType
            : $requestedType;

        if ($isAlias) {
            $params = array_merge($config->getParameters($class), $params);
        }

        $definition = $this->getClassDefinition($class);

        foreach ($definition->getSupertypes() as $supertype) {
            $supertypeParams = $config->getParameters($supertype);

            if (! empty($supertypeParams)) {
                $params = array_merge($supertypeParams, $params);
            }
        }

        // A type configuration may define a parameter should be auto resolved
        // even it was defined earlier
        $params = array_filter($params, static fn($value): bool => $value !== '*');

        return $params;
    }

    /**
     * Check if $type satisfies $requiredType
     *
     * @param string $type The type to check
     * @param string $requiredType The required type to check against
     */
    private function isTypeOf(string $type, string $requiredType): bool
    {
        if ($this->config->isAlias($type)) {
            $type = $this->config->getClassForAlias($type) ?? $type;
        }

        if ($type === $requiredType) {
            return true;
        }

        if (interface_exists($type) && interface_exists($requiredType)) {
            $reflection = new ReflectionClass($type);
            return in_array($requiredType, $reflection->getInterfaceNames());
        }

        if (! $this->definition->hasClass($type)) {
            return false;
        }

        $definition = $this->definition->getClassDefinition($type);
        return in_array($requiredType, $definition->getSupertypes())
            || in_array($requiredType, $definition->getInterfaces());
    }

    private function isUsableType(string $type, string $requiredType): bool
    {
        return $this->isTypeOf($type, $requiredType)
            && ($this->container === null || $this->container->has($type));
    }

    private function getTypeNameFromValue(mixed $value): string
    {
        $type = gettype($value);
        return $this->gettypeMap[$type] ?? $type;
    }

    /**
     * Check if the given value sadisfies the given type
     *
     * @param mixed  $value The value to check
     * @param string $type The typename to check against
     */
    private function isValueOf(mixed $value, string $type): bool
    {
        if (! $this->isBuiltinType($type)) {
            return $value instanceof $type;
        }

        if ($type === 'callable') {
            return is_callable($value);
        }

        if ($type === 'iterable') {
            return is_iterable($value);
        }

        $valueType = $this->getTypeNameFromValue($value);
        $numerics  = ['int', 'float'];

        // PHP accepts float for int and vice versa, as well as numeric string values
        if (in_array($type, $numerics)) {
            return in_array($valueType, $numerics) || (is_string($value) && is_numeric($value));
        }

        return $type === $valueType;
    }

    private function isBuiltinType(string $type): bool
    {
        return in_array($type, $this->builtinTypes);
    }

    /**
     * @see DependencyResolverInterface::setContainer()
     *
     * @return $this
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
        return $this;
    }

    private function isCallableType(string $type): bool
    {
        if ($this->config->isAlias($type)) {
            $type = $this->config->getClassForAlias($type) ?? $type;
        }

        if (! class_exists($type) && ! interface_exists($type)) {
            return false;
        }

        $reflection = new ReflectionClass($type);

        return $reflection->hasMethod('__invoke')
            && $reflection->getMethod('__invoke')->isPublic();
    }

    /**
     * Prepare a candidate for injection
     *
     * If the candidate is usable, its injection representation is returned
     */
    private function prepareInjection(mixed $value, ?string $requiredType): ?InjectionInterface
    {
        if ($value instanceof ValueInjection || $value instanceof TypeInjection) {
            return $value;
        }

        if (! $requiredType) {
            $isAvailableInContainer = is_string($value) && $this->container !== null && $this->container->has($value);
            return $isAvailableInContainer ? new TypeInjection($value) : new ValueInjection($value);
        }

        if (is_string($value) && ! $this->isBuiltinType($requiredType)) {
            return new TypeInjection($value);
        }

        // Classes may implement iterable
        if (is_string($value) && ($requiredType === 'iterable')) {
            return $this->isUsableType($value, 'Traversable') ? new TypeInjection($value) : null;
        }

        // Classes may implement callable, but strings could be callable as well
        if (is_string($value) && ($requiredType === 'callable') && $this->isCallableType($value)) {
            return new TypeInjection($value);
        }

        return $this->isValueOf($value, $requiredType) ? new ValueInjection($value) : null;
    }

    /**
     * {@inheritDoc}
     *
     * @see DependencyResolverInterface::resolveParameters()
     *
     * @throws Exception\UnexpectedValueException
     * @throws Exception\MissingPropertyException
     * @return InjectionInterface[]
     */
    public function resolveParameters(string $requestedType, array $callTimeParameters = []): array
    {
        $definition = $this->getClassDefinition($requestedType);
        $params     = $definition->getParameters();
        $result     = [];

        if (empty($params)) {
            return $result;
        }

        $configuredParameters = $this->getConfiguredParameters($requestedType);

        foreach ($params as $paramInfo) {
            $name = $paramInfo->getName();
            $type = $paramInfo->getType();

            if (isset($callTimeParameters[$name])) {
                $result[$name] = new ValueInjection($callTimeParameters[$name]);
                continue;
            }

            if (isset($configuredParameters[$name]) && ($configuredParameters[$name] !== '*')) {
                $injection = $this->prepareInjection($configuredParameters[$name], $type);

                if (! $injection) {
                    throw new Exception\UnexpectedValueException(sprintf(
                        'Unusable configured injection for parameter "%s" of type "%s"',
                        $name,
                        $type ?? 'null'
                    ));
                }

                $result[$name] = $injection;
                continue;
            }

            if ($type && ! $paramInfo->isBuiltin()) {
                $preference = $this->resolvePreference($type, $requestedType);

                if ($preference) {
                    $result[$name] = new TypeInjection($preference);
                    continue;
                }

                if (
                    $type === ContainerInterface::class
                    || $this->container === null
                    || $this->container->has($type)
                ) {
                    $result[$name] = new TypeInjection($type);
                    continue;
                }
            }

            // The parameter is required, but we can't find anything that is suitable
            if ($paramInfo->isRequired()) {
                $isAlias = $this->config->isAlias($requestedType);
                $class   = $isAlias ? $this->config->getClassForAlias($requestedType) : $requestedType;

                assert(is_string($class));

                throw new Exception\MissingPropertyException(sprintf(
                    'Could not resolve value for parameter "%s" of type %s in class %s (requested as %s)',
                    $name,
                    $type ?: 'any',
                    $class,
                    $requestedType
                ));
            }

            $result[$name] = new ValueInjection($paramInfo->getDefault());
        }

        return $result;
    }

    /**
     * @see DependencyResolverInterface::resolvePreference()
     */
    public function resolvePreference(string $type, ?string $context = null): ?string
    {
        if ($context) {
            $preference = $this->config->getTypePreference($type, $context);

            if ($preference && $this->isUsableType($preference, $type)) {
                return $preference;
            }

            $definition = $this->getClassDefinition($context);

            foreach ($definition->getSupertypes() as $supertype) {
                $preference = $this->config->getTypePreference($type, $supertype);

                if ($preference && $this->isUsableType($preference, $type)) {
                    return $preference;
                }
            }

            foreach ($definition->getInterfaces() as $interface) {
                $preference = $this->config->getTypePreference($type, $interface);

                if ($preference && $this->isUsableType($preference, $type)) {
                    return $preference;
                }
            }
        }

        $preference = $this->config->getTypePreference($type);

        if (! $preference || ! $this->isUsableType($preference, $type)) {
            $preference = null;
        }

        return $preference;
    }
}
