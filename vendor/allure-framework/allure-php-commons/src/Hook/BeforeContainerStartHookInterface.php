<?php

declare(strict_types=1);

namespace Qameta\Allure\Hook;

use Qameta\Allure\Model\ContainerResult;

interface BeforeContainerStartHookInterface extends LifecycleHookInterface
{
    public function beforeContainerStart(ContainerResult $container): void;
}
