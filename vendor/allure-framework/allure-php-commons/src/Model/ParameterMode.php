<?php

declare(strict_types=1);

namespace Qameta\Allure\Model;

use JsonSerializable;

final class ParameterMode extends AbstractEnum implements JsonSerializable
{
    public const MASKED = 'masked';
    public const HIDDEN = 'hidden';

    public static function fromOptionalString(?string $value): ?self
    {
        return match ($value) {
            self::MASKED => self::masked(),
            self::HIDDEN => self::hidden(),
            default => null,
        };
    }

    public static function masked(): self
    {
        return self::create(self::MASKED);
    }

    public static function hidden(): self
    {
        return self::create(self::HIDDEN);
    }

    public function jsonSerialize(): string
    {
        return (string) $this;
    }
}
