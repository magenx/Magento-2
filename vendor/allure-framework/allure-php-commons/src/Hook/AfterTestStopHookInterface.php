<?php

declare(strict_types=1);

namespace Qameta\Allure\Hook;

use Qameta\Allure\Model\TestResult;

interface AfterTestStopHookInterface extends LifecycleHookInterface
{
    public function afterTestStop(TestResult $test): void;
}
