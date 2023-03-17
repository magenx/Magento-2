<?php

declare(strict_types=1);

namespace Qameta\Allure\Hook;

use Qameta\Allure\Model\ContainerResult;

interface BeforeContainerStopHookInterface extends LifecycleHookInterface
{
    public function beforeContainerStop(ContainerResult $container): void;
}
