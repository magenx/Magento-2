<?php

declare(strict_types=1);

namespace Qameta\Allure\Legacy\Annotation;

use Qameta\Allure\Attribute\AttributeInterface;

interface LegacyAnnotationInterface
{
    /**
     * @return list<AttributeInterface>|AttributeInterface
     */
    public function convert(): array|object;
}
