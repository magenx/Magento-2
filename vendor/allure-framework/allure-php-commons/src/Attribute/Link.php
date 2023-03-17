<?php

declare(strict_types=1);

namespace Qameta\Allure\Attribute;

use Attribute;
use Qameta\Allure\Model;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Link extends AbstractLink
{
    public const CUSTOM = Model\LinkType::CUSTOM;
    public const ISSUE = Model\LinkType::ISSUE;
    public const TMS = Model\LinkType::TMS;

    public function __construct(
        ?string $name = null,
        ?string $url = null,
        string $type = self::CUSTOM,
    ) {
        parent::__construct($name, $url, $type);
    }
}
