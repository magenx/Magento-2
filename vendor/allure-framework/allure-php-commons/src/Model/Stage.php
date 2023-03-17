<?php

declare(strict_types=1);

namespace Qameta\Allure\Model;

use JsonSerializable;

final class Stage extends AbstractEnum implements JsonSerializable
{
    private const SCHEDULED = 'scheduled';
    private const RUNNING = 'running';
    private const FINISHED = 'finished';
    private const PENDING = 'pending';
    private const INTERRUPTED = 'interrupted';

    public static function scheduled(): self
    {
        return self::create(self::SCHEDULED);
    }

    public static function running(): self
    {
        return self::create(self::RUNNING);
    }

    public static function finished(): self
    {
        return self::create(self::FINISHED);
    }

    public static function pending(): self
    {
        return self::create(self::PENDING);
    }

    public static function interrupted(): self
    {
        return self::create(self::INTERRUPTED);
    }

    public function jsonSerialize(): string
    {
        return (string) $this;
    }
}
