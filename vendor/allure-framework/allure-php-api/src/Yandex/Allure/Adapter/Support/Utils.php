<?php

namespace Yandex\Allure\Adapter\Support;

use Ramsey\Uuid\Uuid;

trait Utils
{

    /**
     * @return float
     */
    public static function getTimestamp()
    {
        return round(microtime(true) * 1000);
    }

    /**
     * @return string
     */
    public static function generateUUID()
    {
        return Uuid::uuid4()->toString();
    }
}
