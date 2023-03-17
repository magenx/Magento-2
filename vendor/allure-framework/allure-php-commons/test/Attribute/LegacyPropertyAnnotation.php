<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Attribute;

use Doctrine\Common\Annotations\Annotation\Required;
use Qameta\Allure\Legacy\Annotation\LegacyAnnotationInterface;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 * @psalm-suppress MissingConstructor
 */
final class LegacyPropertyAnnotation implements LegacyAnnotationInterface
{
    /**
     * @var string
     * @Required
     */
    public string $value;

    public function convert(): NativePropertyAttribute
    {
        return new NativePropertyAttribute($this->value);
    }
}
