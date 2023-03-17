<?php

declare(strict_types=1);

namespace Yandex\Allure\Adapter\Annotation;

use Qameta\Allure\Attribute\Label as QametaLabel;
use Qameta\Allure\Legacy\Annotation\LegacyAnnotationInterface;

use function array_map;
use function array_merge;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @deprecated Use native PHP attribute {@see \Qameta\Allure\Attribute\Label} (repeatable).
 * @psalm-suppress MissingConstructor
 * @psalm-suppress DeprecatedClass
 */
class Labels implements LegacyAnnotationInterface
{
    /**
     * @var array<\Yandex\Allure\Adapter\Annotation\Label>
     * @psalm-var list<\Yandex\Allure\Adapter\Annotation\Label>
     * @Required
     */
    public array $labels;

    /**
     * @return list<QametaLabel>
     */
    public function convert(): array
    {
        return array_merge(
            ...array_map(
                fn (Label $label) => $label->convert(),
                $this->labels,
            ),
        );
    }
}
