<?php

declare(strict_types=1);

namespace Qameta\Allure\Attribute;

interface DescriptionInterface extends AttributeInterface
{
    public function getValue(): string;

    public function isHtml(): bool;
}
