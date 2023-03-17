<?php

/**
 * @see       https://github.com/laminas/laminas-server for the canonical source repository
 */

namespace Laminas\Server;

use Laminas\Server\Reflection\Exception\InvalidArgumentException;
use Laminas\Server\Reflection\ReflectionClass;
use Laminas\Server\Reflection\ReflectionFunction;
use ReflectionObject;

use function class_exists;
use function function_exists;
use function in_array;
use function is_array;
use function is_object;
use function is_string;
use function sprintf;

/**
 * Reflection for determining method signatures to use with server classes
 */
class Reflection
{
    /**
     * Perform class reflection to create dispatch signatures
     *
     * Creates a {@link \Laminas\Server\Reflection\ClassReflection} object for the class or
     * object provided.
     *
     * If extra arguments should be passed to dispatchable methods, these may
     * be provided as an array to $argv.
     *
     * @param string|object $class Class name or object
     * @param  bool|array $argv Optional arguments to be used during the method call
     * @param string $namespace Optional namespace with which to prefix the
     * method name (used for the signature key). Primarily to avoid collisions,
     * also for XmlRpc namespacing
     * @return ReflectionClass
     * @throws InvalidArgumentException
     */
    public static function reflectClass($class, $argv = false, $namespace = '')
    {
        if (is_object($class)) {
            $reflection = new ReflectionObject($class);
        } elseif (class_exists($class)) {
            $reflection = new \ReflectionClass($class);
        } else {
            throw new InvalidArgumentException('Invalid class or object passed to attachClass()');
        }

        if ($argv && ! is_array($argv)) {
            throw new InvalidArgumentException('Invalid argv argument passed to reflectClass');
        }

        return new ReflectionClass($reflection, $namespace, $argv);
    }

    /**
     * Perform function reflection to create dispatch signatures
     *
     * Creates dispatch prototypes for a function. It returns a
     * {@link Laminas\Server\Reflection\FunctionReflection} object.
     *
     * If extra arguments should be passed to the dispatchable function, these
     * may be provided as an array to $argv.
     *
     * @param string $function Function name
     * @param  null|bool|array $argv Optional arguments to be used during the method call
     * @param string $namespace Optional namespace with which to prefix the
     * function name (used for the signature key). Primarily to avoid
     * collisions, also for XmlRpc namespacing
     * @return ReflectionFunction
     * @throws InvalidArgumentException
     */
    public static function reflectFunction($function, $argv = false, $namespace = '')
    {
        if (! is_string($function) || ! function_exists($function)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid function "%s" passed to reflectFunction',
                $function
            ));
        }

        // Cast null or false values to empty array
        $argv = in_array($argv, [false, null], true) ? [] : $argv;

        if (! is_array($argv)) {
            throw new InvalidArgumentException('Invalid argv argument passed to reflectFunction');
        }

        return new ReflectionFunction(new \ReflectionFunction($function), $namespace, $argv);
    }
}
