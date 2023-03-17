<?php

declare(strict_types=1);

namespace Qameta\Allure\Io;

interface DataSourceInterface
{
    /**
     * @return resource
     */
    public function createStream();
}
