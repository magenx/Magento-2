<?php

declare(strict_types=1);

namespace Yandex\Allure\Adapter\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;
use Qameta\Allure\Attribute\Feature;
use Qameta\Allure\Legacy\Annotation\LegacyAnnotationInterface;

use function array_map;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 * @deprecated Use native PHP attribute {@see \Qameta\Allure\Annotation\Features}
 * @psalm-suppress MissingConstructor
 */
class Features implements LegacyAnnotationInterface
{
    /**
     * @var list<string>
     * @Required
     */
    public array $featureNames;

    public function getFeatureNames(): array
    {
        return $this->featureNames;
    }

    /**
     * @return list<Feature>
     */
    public function convert(): array
    {
        return array_map(
            fn (string $name) => new Feature($name),
            $this->featureNames,
        );
    }
}
