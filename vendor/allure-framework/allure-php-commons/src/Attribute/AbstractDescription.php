<?php

declare(strict_types=1);

namespace Qameta\Allure\Attribute;

abstract class AbstractDescription implements DescriptionInterface
{
    public function __construct(
        private string $value,
        private bool $isHtml,
    ) {
    }

    final public function getValue(): string
    {
        return $this->value;
    }

    final public function isHtml(): bool
    {
        return $this->isHtml;
    }
}
