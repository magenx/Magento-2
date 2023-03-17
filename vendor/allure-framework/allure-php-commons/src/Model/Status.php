<?php

namespace Qameta\Allure\Model;

use JsonSerializable;

final class Status extends AbstractEnum implements JsonSerializable
{
    private const FAILED = 'failed';
    private const BROKEN = 'broken';
    private const PASSED = 'passed';
    private const SKIPPED = 'skipped';

    public static function failed(): self
    {
        return self::create(self::FAILED);
    }

    public static function broken(): self
    {
        return self::create(self::BROKEN);
    }

    public static function passed(): self
    {
        return self::create(self::PASSED);
    }

    public static function skipped(): self
    {
        return self::create(self::SKIPPED);
    }

    public function jsonSerialize(): string
    {
        return (string) $this;
    }
}
