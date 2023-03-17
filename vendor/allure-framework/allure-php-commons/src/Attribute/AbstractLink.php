<?php

declare(strict_types=1);

namespace Qameta\Allure\Attribute;

abstract class AbstractLink implements LinkInterface
{
    public function __construct(
        private ?string $name = null,
        private ?string $url = null,
        private ?string $type = null,
    ) {
    }

    final public function getName(): ?string
    {
        return $this->name;
    }

    final public function getUrl(): ?string
    {
        return $this->url;
    }

    final public function getType(): ?string
    {
        return $this->type;
    }
}
