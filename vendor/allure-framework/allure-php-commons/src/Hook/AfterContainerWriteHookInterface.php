<?php

declare(strict_types=1);

namespace Qameta\Allure\Hook;

use Qameta\Allure\Model\ContainerResult;

interface AfterContainerWriteHookInterface extends LifecycleHookInterface
{
    public function afterContainerWrite(ContainerResult $container): void;
}
