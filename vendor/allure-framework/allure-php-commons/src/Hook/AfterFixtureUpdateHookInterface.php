<?php

declare(strict_types=1);

namespace Qameta\Allure\Hook;

use Qameta\Allure\Model\FixtureResult;

interface AfterFixtureUpdateHookInterface extends LifecycleHookInterface
{
    public function afterFixtureUpdate(FixtureResult $fixture): void;
}
