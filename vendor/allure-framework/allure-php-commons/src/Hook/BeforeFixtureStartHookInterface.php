<?php

declare(strict_types=1);

namespace Qameta\Allure\Hook;

use Qameta\Allure\Model\FixtureResult;

interface BeforeFixtureStartHookInterface extends LifecycleHookInterface
{
    public function beforeFixtureStart(FixtureResult $fixture): void;
}
