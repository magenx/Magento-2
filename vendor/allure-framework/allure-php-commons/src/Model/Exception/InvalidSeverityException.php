<?php

declare(strict_types=1);

namespace Qameta\Allure\Model\Exception;

use DomainException;
use Throwable;

final class InvalidSeverityException extends DomainException
{
    public function __construct(private string $severity, Throwable $previous = null)
    {
        parent::__construct("Invalid severity: $this->severity", 0, $previous);
    }

    public function getSeverity(): string
    {
        return $this->severity;
    }
}
