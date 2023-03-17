<?php

declare(strict_types=1);

namespace Yandex\Allure\Adapter\Support;

use Qameta\Allure\Allure;
use Qameta\Allure\StepContextInterface;
use Throwable;

/**
 * @deprecated Please use {@see Allure::runStep()} method directly instead of this trait.
 */
trait StepSupport
{
    /**
     * Adds a simple step to current test case
     *
     * @param string                               $name  step name
     * @param callable(StepContextInterface):mixed $logic anonymous function containing the entire step logic.
     * @param string|null                          $title an optional title for the step
     * @return mixed
     * @throws Throwable
     */
    public function executeStep(string $name, callable $logic, ?string $title = null): mixed
    {
        return Allure::runStep($logic, $title ?? $name);
    }
}
