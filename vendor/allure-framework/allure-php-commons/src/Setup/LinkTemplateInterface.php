<?php

declare(strict_types=1);

namespace Qameta\Allure\Setup;

interface LinkTemplateInterface
{
    public function buildUrl(?string $name): ?string;
}
