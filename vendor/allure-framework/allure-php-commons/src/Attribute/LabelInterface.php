<?php

declare(strict_types=1);

namespace Qameta\Allure\Attribute;

interface LabelInterface extends AttributeInterface
{
    public function getName(): string;

    public function getValue(): ?string;
}
