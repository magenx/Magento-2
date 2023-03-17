<?php

declare(strict_types=1);

namespace Qameta\Allure\Attribute;

abstract class AbstractLabel implements LabelInterface
{
    public function __construct(
        private string $name,
        private ?string $value = null,
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
}
