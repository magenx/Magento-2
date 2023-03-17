<?php

declare(strict_types=1);

namespace Qameta\Allure;

use Qameta\Allure\Model\ParameterMode;

interface StepContextInterface
{
    public function name(string $name): void;

    public function parameter(
        string $name,
        ?string $value,
        bool $excluded = false,
        ?ParameterMode $mode = null,
    ): ?string;
}
