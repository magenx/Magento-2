<?php

declare(strict_types=1);

namespace Qameta\Allure\Io;

use JsonException;
use JsonSerializable;

use function json_encode;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_UNICODE;

final class DataSourceFactory
{
    private function __construct()
    {
    }

    public static function fromFile(string $file): DataSourceInterface
    {
        return self::fromStream("file://{$file}");
    }

    public static function fromStream(string $link): DataSourceInterface
    {
        return new StreamDataSource($link);
    }

    public static function fromString(string $data): DataSourceInterface
    {
        return new StringDataSource($data);
    }

    /**
     * @throws JsonException
     */
    public static function fromSerializable(JsonSerializable $object): DataSourceInterface
    {
        return self::fromString(
            json_encode(
                $object,
                JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR,
            )
        );
    }
}
