<?php

declare(strict_types=1);

namespace Qameta\Allure\Hook;

use Qameta\Allure\Model\StepResult;

interface BeforeStepUpdateHookInterface extends LifecycleHookInterface
{
    public function beforeStepUpdate(StepResult $step): void;
}
