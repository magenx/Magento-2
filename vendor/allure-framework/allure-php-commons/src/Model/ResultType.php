<?php

declare(strict_types=1);

namespace Qameta\Allure\Model;

final class ResultType extends AbstractEnum
{
    private const UNKNOWN = 'unknown';
    private const CONTAINER = 'container';
    private const FIXTURE = 'fixture';
    private const TEST = 'test';
    private const STEP = 'step';
    private const ATTACHMENT = 'attachment';
    private const EXECUTABLE_CONTEXT = 'executable_context';

    public static function unknown(): self
    {
        return self::create(self::UNKNOWN);
    }

    public static function container(): self
    {
        return self::create(self::CONTAINER);
    }

    public static function fixture(): self
    {
        return self::create(self::FIXTURE);
    }

    public static function test(): self
    {
        return self::create(self::TEST);
    }

    public static function step(): self
    {
        return self::create(self::STEP);
    }

    public static function attachment(): self
    {
        return self::create(self::ATTACHMENT);
    }

    public static function executableContext(): self
    {
        return self::create(self::EXECUTABLE_CONTEXT);
    }
}
