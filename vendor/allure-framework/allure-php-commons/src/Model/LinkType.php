<?php

declare(strict_types=1);

namespace Qameta\Allure\Model;

use JsonSerializable;

final class LinkType extends AbstractEnum implements JsonSerializable
{
    public const ISSUE = "issue";
    public const TMS = "tms";
    public const CUSTOM = "custom";

    public static function fromOptionalString(?string $value): self
    {
        return match ($value) {
            self::ISSUE => self::issue(),
            self::TMS => self::tms(),
            default => self::custom(),
        };
    }

    public static function issue(): self
    {
        return self::create(self::ISSUE);
    }

    public static function tms(): self
    {
        return self::create(self::TMS);
    }

    public static function custom(): self
    {
        return self::create(self::CUSTOM);
    }

    public function jsonSerialize(): string
    {
        return (string) $this;
    }
}
