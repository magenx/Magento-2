<?php

declare(strict_types=1);

namespace Qameta\Allure\Hook;

use Qameta\Allure\Model\FixtureResult;

interface AfterFixtureStopHookInterface extends LifecycleHookInterface
{
    public function afterFixtureStop(FixtureResult $fixture): void;
}
