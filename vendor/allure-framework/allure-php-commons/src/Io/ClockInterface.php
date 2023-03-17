<?php

declare(strict_types=1);

namespace Qameta\Allure\Io;

use DateTimeImmutable;

interface ClockInterface
{
    public function now(): DateTimeImmutable;
}
