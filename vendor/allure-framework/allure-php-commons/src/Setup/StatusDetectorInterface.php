<?php

declare(strict_types=1);

namespace Qameta\Allure\Setup;

use Qameta\Allure\Model\Status;
use Qameta\Allure\Model\StatusDetails;
use Throwable;

interface StatusDetectorInterface
{
    public function getStatus(Throwable $error): ?Status;

    public function getStatusDetails(Throwable $error): ?StatusDetails;
}
