<?php

declare(strict_types=1);

namespace Qameta\Allure\Exception;

use LogicException;
use Qameta\Allure\Allure;
use Throwable;

final class OutputDirectoryUndefinedException extends LogicException
{
    public function __construct(?Throwable $previous = null)
    {
        $class = Allure::class;
        parent::__construct(
            "Output directory is not set for Allure. " .
            "Please call {$class}::setOutputDirectory() method before accessing Allure lifecycle object.",
            0,
            $previous
        );
    }
}
