<?php

declare(strict_types=1);

namespace Qameta\Allure\Exception;

use LogicException;
use Throwable;

final class ActiveContainerNotFoundException extends LogicException
{
    public function __construct(Throwable $previous = null)
    {
        parent::__construct("Active container not found", 0, $previous);
    }
}
