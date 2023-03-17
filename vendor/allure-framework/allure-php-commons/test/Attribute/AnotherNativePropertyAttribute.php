<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Attribute;

use Attribute;
use Qameta\Allure\Attribute\AttributeInterface;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class AnotherNativePropertyAttribute implements AttributeInterface
{
    public function __construct(private string $value)
    {
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
