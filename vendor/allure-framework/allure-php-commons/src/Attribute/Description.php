<?php

declare(strict_types=1);

namespace Qameta\Allure\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class Description extends AbstractDescription
{
    public function __construct(string $value, bool $isHtml = false)
    {
        parent::__construct($value, $isHtml);
    }
}
