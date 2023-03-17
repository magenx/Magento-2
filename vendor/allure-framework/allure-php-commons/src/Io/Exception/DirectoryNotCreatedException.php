<?php

declare(strict_types=1);

namespace Qameta\Allure\Io\Exception;

use RuntimeException;
use Throwable;

final class DirectoryNotCreatedException extends RuntimeException
{
    public function __construct(
        private string $directory,
        ?string $message = null,
        Throwable $previous = null,
    ) {
        parent::__construct($this->buildMessage($message), 0, $previous);
    }

    private function buildMessage(?string $message): string
    {
        $baseMessage = "Failed to create directory {$this->directory}";

        return isset($message)
            ? "{$baseMessage}: {$message}"
            : $baseMessage;
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }
}
