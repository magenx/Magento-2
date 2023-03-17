<?php

declare(strict_types=1);

namespace Qameta\Allure\Hook;

use Qameta\Allure\Model\TestResult;

interface BeforeTestScheduleHookInterface extends LifecycleHookInterface
{
    public function beforeTestSchedule(TestResult $test): void;
}
