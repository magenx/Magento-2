<?php

declare(strict_types=1);

namespace Qameta\Allure\Io\Exception;

use RuntimeException;
use Throwable;

final class InvalidDirectoryException extends RuntimeException
{
    public function __construct(
        private string $directory,
        Throwable $previous = null,
    ) {
        parent::__construct("Not a directory: {$this->directory}", 0, $previous);
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }
}
