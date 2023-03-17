<?php

declare(strict_types=1);

namespace Yandex\Allure\Adapter\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;
use Qameta\Allure\Attribute\AllureId as QametaAllureId;
use Qameta\Allure\Legacy\Annotation\LegacyAnnotationInterface;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @deprecated Use native PHP attribute {@see \Qameta\Allure\Attribute\AllureId}
 * @psalm-suppress MissingConstructor
 */
class AllureId implements LegacyAnnotationInterface
{
    /**
     * @var string
     * @Required
     */
    public string $value;

    public function convert(): QametaAllureId
    {
        return new QametaAllureId($this->value);
    }
}
