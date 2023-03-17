<?php

declare(strict_types=1);

namespace Qameta\Allure\Hook;

use Qameta\Allure\Model\FixtureResult;

interface BeforeFixtureStopHookInterface extends LifecycleHookInterface
{
    public function beforeFixtureStop(FixtureResult $fixture): void;
}
