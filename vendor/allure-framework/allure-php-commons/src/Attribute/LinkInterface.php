<?php

declare(strict_types=1);

namespace Qameta\Allure\Attribute;

interface LinkInterface extends AttributeInterface
{
    public function getName(): ?string;

    public function getUrl(): ?string;

    public function getType(): ?string;
}
