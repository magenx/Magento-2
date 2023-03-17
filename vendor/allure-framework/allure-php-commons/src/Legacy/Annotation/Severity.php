<?php

declare(strict_types=1);

namespace Yandex\Allure\Adapter\Annotation;

use Qameta\Allure\Attribute\Severity as QametaSeverity;
use Qameta\Allure\Legacy\Annotation\LegacyAnnotationInterface;
use Yandex\Allure\Adapter\Model\SeverityLevel;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @deprecated Use native PHP attribute {@see \Qameta\Allure\Attribute\Severity}
 */
class Severity implements LegacyAnnotationInterface
{
    /**
     * @psalm-suppress DeprecatedClass
     */
    public string $level = SeverityLevel::NORMAL;

    public function convert(): QametaSeverity
    {
        return new QametaSeverity($this->level);
    }
}
