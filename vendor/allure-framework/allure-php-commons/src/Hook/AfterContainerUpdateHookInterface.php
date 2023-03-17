<?php

declare(strict_types=1);

namespace Qameta\Allure\Hook;

use Qameta\Allure\Model\ContainerResult;

interface AfterContainerUpdateHookInterface extends LifecycleHookInterface
{
    public function afterContainerUpdate(ContainerResult $container): void;
}
