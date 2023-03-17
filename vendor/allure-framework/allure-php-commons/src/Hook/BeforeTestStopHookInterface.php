<?php

declare(strict_types=1);

namespace Qameta\Allure\Hook;

use Qameta\Allure\Model\TestResult;

interface BeforeTestStopHookInterface extends LifecycleHookInterface
{
    public function beforeTestStop(TestResult $test): void;
}
