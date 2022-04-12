<?php

declare(strict_types=1);

namespace Laminas\Di\Resolver;

use Psr\Container\ContainerInterface;

use function trigger_error;
use function var_export;

use const E_USER_DEPRECATED;

/**
 * Wrapper for types that should be looked up for injection
 */
final class TypeInjection implements InjectionInterface
{
    /**
     * Holds the type name to look up
     *
     * @var string
     */
    private $type;

    /**
     * Constructor
     */
    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function export(): string
    {
        return var_export($this->type, true);
    }

    public function isExportable(): bool
    {
        return true;
    }

    /** @return mixed */
    public function toValue(ContainerInterface $container)
    {
        return $container->get($this->type);
    }

    /**
     * Reflects the type name
     */
    public function __toString(): string
    {
        return $this->type;
    }

    /**
     * Get the type name to look up for injection
     *
     * @deprecated Since 3.1.0
     *
     * @see toValue()
     * @see export()
     * @see __toString()
     *
     * @codeCoverageIgnore
     */
    public function getType(): string
    {
        trigger_error(__METHOD__ . ' is deprecated. Please migrate to __toString()', E_USER_DEPRECATED);
        return $this->type;
    }
}
