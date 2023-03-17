<?php

declare(strict_types=1);

namespace Qameta\Allure\Model;

use JsonSerializable;

final class Parameter implements JsonSerializable
{
    use JsonSerializableTrait;

    public function __construct(
        protected string $name,
        protected ?string $value = null,
        protected ?bool $excluded = null,
        protected ?ParameterMode $mode = null,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getExcluded(): ?bool
    {
        return $this->excluded;
    }

    public function setExcluded(?bool $excluded): self
    {
        $this->excluded = $excluded;

        return $this;
    }

    public function getMode(): ?ParameterMode
    {
        return $this->mode;
    }

    public function setMode(?ParameterMode $mode): self
    {
        $this->mode = $mode;

        return $this;
    }
}
