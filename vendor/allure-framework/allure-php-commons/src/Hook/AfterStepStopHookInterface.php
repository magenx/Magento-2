<?php

declare(strict_types=1);

namespace Qameta\Allure\Hook;

use Qameta\Allure\Model\StepResult;

interface AfterStepStopHookInterface extends LifecycleHookInterface
{
    public function afterStepStop(StepResult $step): void;
}
