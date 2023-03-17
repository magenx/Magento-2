<?php

declare(strict_types=1);

namespace Qameta\Allure\Model;

use Stringable;

abstract class AbstractEnum implements Stringable
{
    /**
     * @var array<string, static>
     */
    private static array $instances = [];

    final protected function __construct(private string $value)
    {
    }

    protected static function create(string $value): static
    {
        return self::$instances[$value] ??= new static($value);
    }

    final public function __toString(): string
    {
        return $this->value;
    }
}
