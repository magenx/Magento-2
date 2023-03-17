<?php

declare(strict_types=1);

namespace Qameta\Allure\Hook;

use Qameta\Allure\Model\TestResult;

interface BeforeTestWriteHookInterface extends LifecycleHookInterface
{
    public function beforeTestWrite(TestResult $test): void;
}
