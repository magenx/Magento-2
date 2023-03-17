<?php

declare(strict_types=1);

namespace Qameta\Allure\Hook;

use Qameta\Allure\Model\ContainerResult;

interface BeforeContainerWriteHookInterface extends LifecycleHookInterface
{
    public function beforeContainerWrite(ContainerResult $container): void;
}
