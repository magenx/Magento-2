<?php

declare(strict_types=1);

namespace Laminas\Di;

/**
 * Interface that defines the dependency injector
 */
interface InjectorInterface
{
    /**
     * Check if this dependency injector can handle the given class
     */
    public function canCreate(string $name): bool;

    /**
     * Create a new instance of a class or alias
     *
     * @param array $options Parameters used for instanciation
     * @return object The resulting instace
     * @throws Exception\ExceptionInterface When an error occours during instanciation.
     */
    public function create(string $name, array $options = []);
}
