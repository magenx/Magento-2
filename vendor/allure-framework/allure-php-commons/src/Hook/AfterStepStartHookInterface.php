<?php

declare(strict_types=1);

namespace Qameta\Allure\Hook;

use Qameta\Allure\Model\StepResult;

interface AfterStepStartHookInterface extends LifecycleHookInterface
{
    public function afterStepStart(StepResult $step): void;
}
