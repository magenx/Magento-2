<?php

declare(strict_types=1);

namespace Qameta\Allure\Attribute;

use Qameta\Allure\Model;

final class ParameterMode
{
    public const HIDDEN = Model\ParameterMode::HIDDEN;
    public const MASKED = Model\ParameterMode::MASKED;
}
