<?php

declare(strict_types=1);

namespace Yandex\Allure\Adapter\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;
use Qameta\Allure\Attribute\Label as QametaLabel;
use Qameta\Allure\Legacy\Annotation\LegacyAnnotationInterface;

use function array_map;

/**
 * @Annotation
 * @Target({"METHOD", "ANNOTATION"})
 * @deprecated Use native PHP attribute {@see \Qameta\Allure\Attribute\Label}
 * @psalm-suppress MissingConstructor
 */
class Label implements LegacyAnnotationInterface
{
    /**
     * @Required
     */
    public string $name;

    /**
     * @var array
     * @psalm-var list<string>
     * @Required
     */
    public array $values;

    /**
     * @return list<QametaLabel>
     */
    public function convert(): array
    {
        return array_map(
            fn (string $value) => new QametaLabel($this->name, $value),
            $this->values,
        );
    }
}
