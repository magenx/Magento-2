<?php

declare(strict_types=1);

namespace Qameta\Allure\Io;

use Qameta\Allure\Io\Exception\StreamOpenFailedException;

use function error_clear_last;
use function error_get_last;
use function fopen;

final class StreamDataSource implements DataSourceInterface
{
    public function __construct(private string $link)
    {
    }

    /**
     * @return resource
     */
    public function createStream()
    {
        error_clear_last();
        $stream = @fopen($this->link, 'rb');

        if (false === $stream) {
            $error = error_get_last();

            throw new StreamOpenFailedException($this->link, $error['message'] ?? null);
        }

        return $stream;
    }
}
