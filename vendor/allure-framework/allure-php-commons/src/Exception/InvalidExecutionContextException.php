<?php

declare(strict_types=1);

namespace Qameta\Allure\Exception;

use DomainException;
use Qameta\Allure\Model\ExecutionContextInterface;
use Throwable;

final class InvalidExecutionContextException extends DomainException
{
    public function __construct(
        private ExecutionContextInterface $context,
        ?Throwable $previous = null,
    ) {
        parent::__construct("Invalid execution context: " . $this->context::class, 0, $previous);
    }

    public function getContext(): ExecutionContextInterface
    {
        return $this->context;
    }
}
