<?php

declare(strict_types=1);

namespace Qameta\Allure\Io\Exception;

use RuntimeException;
use Throwable;

final class IoFailedException extends RuntimeException
{
    public function __construct(?string $message = null, Throwable $previous = null)
    {
        parent::__construct($this->buildMessage($message), 0, $previous);
    }

    private function buildMessage(?string $message): string
    {
        $baseMessage = "IO operation failed";

        return isset($message)
            ? "{$baseMessage}: {$message}"
            : $baseMessage;
    }
}
