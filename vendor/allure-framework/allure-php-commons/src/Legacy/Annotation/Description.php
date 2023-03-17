<?php

declare(strict_types=1);

namespace Yandex\Allure\Adapter\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;
use Qameta\Allure\Attribute\Description as QametaDescription;
use Qameta\Allure\Legacy\Annotation\LegacyAnnotationInterface;
use Yandex\Allure\Adapter\Model\DescriptionType;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 * @deprecated Use native PHP attribute {@see \Qameta\Allure\Attribute\Description}
 * @psalm-suppress MissingConstructor
 */
class Description implements LegacyAnnotationInterface
{
    /**
     * @var string
     * @Required
     */
    public string $value;

    /**
     * @var string
     * @psalm-suppress DeprecatedClass
     */
    public string $type = DescriptionType::TEXT;

    public function convert(): QametaDescription
    {
        /** @psalm-suppress DeprecatedClass */
        return new QametaDescription(
            $this->value,
            DescriptionType::HTML == $this->type,
        );
    }
}
