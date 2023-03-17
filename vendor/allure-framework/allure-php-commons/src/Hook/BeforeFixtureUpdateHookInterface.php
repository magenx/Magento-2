<?php

declare(strict_types=1);

namespace Qameta\Allure\Hook;

use Qameta\Allure\Model\FixtureResult;

interface BeforeFixtureUpdateHookInterface extends LifecycleHookInterface
{
    public function beforeFixtureUpdate(FixtureResult $fixture): void;
}
