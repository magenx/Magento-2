<?php

declare(strict_types=1);

namespace Qameta\Allure\Attribute;

interface DisplayNameInterface extends AttributeInterface
{
    public function getValue(): string;
}
