<?php

declare(strict_types=1);

namespace Yandex\Allure\Adapter\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @deprecated This annotation is not used anymore.
 */
class TestType
{
    public string $type = "screenshotDiff";
}
