<?php

declare(strict_types=1);

namespace Qameta\Allure\Hook;

use Qameta\Allure\Model\ContainerResult;

interface AfterContainerStartHookInterface extends LifecycleHookInterface
{
    public function afterContainerStart(ContainerResult $container): void;
}
