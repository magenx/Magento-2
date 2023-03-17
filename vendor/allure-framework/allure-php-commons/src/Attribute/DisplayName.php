<?php

declare(strict_types=1);

namespace Qameta\Allure\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::TARGET_FUNCTION)]
final class DisplayName extends AbstractDisplayName
{
}
