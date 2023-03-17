<?php

declare(strict_types=1);

namespace Qameta\Allure\Hook;

use Qameta\Allure\Model\ContainerResult;

interface AfterContainerStopHookInterface extends LifecycleHookInterface
{
    public function afterContainerStop(ContainerResult $container): void;
}
