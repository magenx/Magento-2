<?php

declare(strict_types=1);

namespace Qameta\Allure\Attribute;

use Attribute;
use JetBrains\PhpStorm\ExpectedValues;
use Qameta\Allure\Model;

#[Attribute(Attribute::TARGET_METHOD)]
final class Severity extends AbstractLabel
{
    public const BLOCKER = Model\Severity::BLOCKER;
    public const CRITICAL = Model\Severity::CRITICAL;
    public const NORMAL = Model\Severity::NORMAL;
    public const MINOR = Model\Severity::MINOR;
    public const TRIVIAL = Model\Severity::TRIVIAL;

    public function __construct(
        #[ExpectedValues(flagsFromClass: self::class)]
        ?string $value = null,
    ) {
        parent::__construct(Label::SEVERITY, $value ?? self::NORMAL);
    }
}
