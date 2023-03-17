<?php

declare(strict_types=1);

namespace Qameta\Allure\Io;

use Qameta\Allure\Io\Exception\IoFailedException;
use Qameta\Allure\Io\Exception\StreamOpenFailedException;
use Qameta\Allure\Io\Exception\StreamWriteFailedException;
use Throwable;

use function error_clear_last;
use function error_get_last;
use function fclose;
use function fopen;
use function fwrite;
use function rewind;

/**
 * @internal
 */
final class StringDataSource implements DataSourceInterface
{
    public function __construct(private string $data)
    {
    }

    /**
     * @return resource
     */
    public function createStream()
    {
        error_clear_last();
        $link = 'php://temp';
        $stream = @fopen($link, 'r+b');
        if (false === $stream) {
            $error = error_get_last();
            throw new StreamOpenFailedException($link, $error['message'] ?? null);
        }
        try {
            error_clear_last();
            if (false === @fwrite($stream, $this->data)) {
                $error = error_get_last();
                throw new StreamWriteFailedException($error['message'] ?? null);
            }
            error_clear_last();
            if (false == @rewind($stream)) {
                $error = error_get_last();
                throw new IoFailedException($error['message'] ?? null);
            }
        } catch (Throwable $e) {
            @fclose($stream);
            throw $e;
        }

        return $stream;
    }
}
