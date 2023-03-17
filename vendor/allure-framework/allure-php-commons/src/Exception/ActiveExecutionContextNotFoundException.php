<?php

declare(strict_types=1);

namespace Qameta\Allure\Exception;

use LogicException;
use Throwable;

final class ActiveExecutionContextNotFoundException extends LogicException
{
    public function __construct(Throwable $previous = null)
    {
        parent::__construct("Active test, fixture or step not found", 0, $previous);
    }
}
