<?php

declare(strict_types=1);

namespace Yandex\Allure\Adapter\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;
use Qameta\Allure\Attribute\DisplayName as QametaDisplayName;
use Qameta\Allure\Legacy\Annotation\LegacyAnnotationInterface;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 * @deprecated Use native PHP attribute {@see \Qameta\Allure\Attribute\DisplayName}
 * @psalm-suppress MissingConstructor
 */
class Title implements LegacyAnnotationInterface
{
    /**
     * @Required
     */
    public string $value;

    public function convert(): QametaDisplayName
    {
        return new QametaDisplayName($this->value);
    }
}
