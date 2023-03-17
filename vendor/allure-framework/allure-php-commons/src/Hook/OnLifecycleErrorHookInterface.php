<?php

declare(strict_types=1);

namespace Qameta\Allure\Hook;

use Throwable;

interface OnLifecycleErrorHookInterface extends LifecycleHookInterface
{
    public function onLifecycleError(Throwable $error): void;
}
