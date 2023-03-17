<?php

declare(strict_types=1);

namespace Qameta\Allure\Hook;

use Qameta\Allure\Model\StepResult;

interface BeforeStepStartHookInterface extends LifecycleHookInterface
{
    public function beforeStepStart(StepResult $step): void;
}
