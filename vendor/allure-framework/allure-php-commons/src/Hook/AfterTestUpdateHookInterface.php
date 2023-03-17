<?php

declare(strict_types=1);

namespace Qameta\Allure\Hook;

use Qameta\Allure\Model\TestResult;

interface AfterTestUpdateHookInterface extends LifecycleHookInterface
{
    public function afterTestUpdate(TestResult $test): void;
}
