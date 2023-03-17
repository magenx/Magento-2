<?php

declare(strict_types=1);

namespace Qameta\Allure\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Issue extends AbstractLink
{
    public function __construct(?string $name = null, ?string $url = null)
    {
        parent::__construct($name, $url, Link::ISSUE);
    }
}
