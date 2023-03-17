<?php

declare(strict_types=1);

namespace Qameta\Allure\Hook;

use Qameta\Allure\Model\StepResult;

interface AfterStepUpdateHookInterface extends LifecycleHookInterface
{
    public function afterStepUpdate(StepResult $step): void;
}
