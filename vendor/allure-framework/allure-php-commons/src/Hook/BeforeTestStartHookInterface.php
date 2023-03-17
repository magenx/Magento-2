<?php

declare(strict_types=1);

namespace Qameta\Allure\Hook;

use Qameta\Allure\Model\TestResult;

interface BeforeTestStartHookInterface extends LifecycleHookInterface
{
    public function beforeTestStart(TestResult $test): void;
}
