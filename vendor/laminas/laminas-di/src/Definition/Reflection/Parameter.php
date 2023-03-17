<?php

declare(strict_types=1);

namespace Laminas\Di\Definition\Reflection;

use Laminas\Di\Definition\ParameterInterface;
use Laminas\Di\Exception\UnsupportedReflectionTypeException;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * This class specifies a method parameter for the di definition
 */
class Parameter implements ParameterInterface
{
    /** @var ReflectionParameter */
    protected $reflection;

    public function __construct(ReflectionParameter $reflection)
    {
        $this->reflection = $reflection;
    }

    /**
     * {@inheritDoc}
     *
     * @see ParameterInterface::getDefault()
     *
     * @return mixed
     */
    public function getDefault()
    {
        return $this->reflection->getDefaultValue();
    }

    /**
     * {@inheritDoc}
     *
     * @see ParameterInterface::getName()
     */
    public function getName(): string
    {
        return $this->reflection->getName();
    }

    /**
     * {@inheritDoc}
     *
     * @see ParameterInterface::getPosition()
     */
    public function getPosition(): int
    {
        return $this->reflection->getPosition();
    }

    /**
     * {@inheritDoc}
     *
     * @see ParameterInterface::getType()
     *
     * @throws UnsupportedReflectionTypeException
     */
    public function getType(): ?string
    {
        $type = $this->reflection->getType();

        if (! $type) {
            return null;
        }

        if (! $type instanceof ReflectionNamedType) {
            throw UnsupportedReflectionTypeException::fromUnionOrIntersectionType($type);
        }

        return $type->getName();
    }

    /**
     * {@inheritDoc}
     *
     * @see ParameterInterface::isRequired()
     */
    public function isRequired(): bool
    {
        return ! $this->reflection->isOptional();
    }

    /**
     * {@inheritDoc}
     *
     * @see ParameterInterface::isScalar()
     *
     * @throws UnsupportedReflectionTypeException
     */
    public function isBuiltin(): bool
    {
        $type = $this->reflection->getType();

        if (! $type) {
            return false;
        }

        if (! $type instanceof ReflectionNamedType) {
            throw UnsupportedReflectionTypeException::fromUnionOrIntersectionType($type);
        }

        return $type->isBuiltin();
    }
}
