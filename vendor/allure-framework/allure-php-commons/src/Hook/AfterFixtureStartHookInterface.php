<?php

declare(strict_types=1);

namespace Qameta\Allure\Hook;

use Qameta\Allure\Model\FixtureResult;

interface AfterFixtureStartHookInterface extends LifecycleHookInterface
{
    public function afterFixtureStart(FixtureResult $fixture): void;
}
