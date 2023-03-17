<?php

declare(strict_types=1);

namespace Qameta\Allure\Hook;

use Qameta\Allure\Model\StepResult;

interface BeforeStepStopHookInterface extends LifecycleHookInterface
{
    public function beforeStepStop(StepResult $step): void;
}
