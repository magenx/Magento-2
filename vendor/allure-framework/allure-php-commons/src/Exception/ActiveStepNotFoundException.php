<?php

declare(strict_types=1);

namespace Qameta\Allure\Exception;

use LogicException;
use Throwable;

final class ActiveStepNotFoundException extends LogicException
{
    public function __construct(Throwable $previous = null)
    {
        parent::__construct("Active step not found", 0, $previous);
    }
}
