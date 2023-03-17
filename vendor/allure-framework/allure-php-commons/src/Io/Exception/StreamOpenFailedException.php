<?php

declare(strict_types=1);

namespace Qameta\Allure\Io\Exception;

use RuntimeException;
use Throwable;

final class StreamOpenFailedException extends RuntimeException
{
    public function __construct(
        private string $link,
        ?string $message = null,
        Throwable $previous = null,
    ) {
        parent::__construct(
            $this->buildMessage($message),
            0,
            $previous,
        );
    }

    private function buildMessage(?string $message): string
    {
        $baseMessage = "Failed to open stream {$this->link}";

        return isset($message)
            ? "{$baseMessage}: {$message}"
            : $baseMessage;
    }

    public function getLink(): string
    {
        return $this->link;
    }
}
