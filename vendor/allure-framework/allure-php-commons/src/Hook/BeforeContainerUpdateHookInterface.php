<?php

declare(strict_types=1);

namespace Qameta\Allure\Hook;

use Qameta\Allure\Model\ContainerResult;

interface BeforeContainerUpdateHookInterface extends LifecycleHookInterface
{
    public function beforeContainerUpdate(ContainerResult $container): void;
}
