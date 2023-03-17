<?php

declare(strict_types=1);

namespace Qameta\Allure\Setup;

use function sprintf;

final class LinkTemplate implements LinkTemplateInterface
{
    public function __construct(
        private string $template,
    ) {
    }

    public function buildUrl(?string $name): ?string
    {
        return isset($name)
            ? sprintf($this->template, $name)
            : null;
    }
}
