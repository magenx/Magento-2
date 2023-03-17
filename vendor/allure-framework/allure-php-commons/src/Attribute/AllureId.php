<?php

declare(strict_types=1);

namespace Qameta\Allure\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class AllureId extends AbstractLabel
{
    public function __construct(string $value)
    {
        parent::__construct(Label::ALLURE_ID, $value);
    }
}
