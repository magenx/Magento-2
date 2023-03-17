<?php

declare(strict_types=1);

namespace Qameta\Allure\Attribute;

abstract class AbstractParameter implements ParameterInterface
{
    public function __construct(
        private string $name,
        private ?string $value,
        private ?bool $excluded = null,
        private ?string $mode = null,
    ) {
    }

    final public function getName(): string
    {
        return $this->name;
    }

    final public function getValue(): ?string
    {
        return $this->value;
    }

    final public function getExcluded(): ?bool
    {
        return $this->excluded;
    }

    final public function getMode(): ?string
    {
        return $this->mode;
    }
}
