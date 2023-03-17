<?php

declare(strict_types=1);

namespace Qameta\Allure\Hook;

use Qameta\Allure\Model\TestResult;

interface BeforeTestUpdateHookInterface extends LifecycleHookInterface
{
    public function beforeTestUpdate(TestResult $test): void;
}
