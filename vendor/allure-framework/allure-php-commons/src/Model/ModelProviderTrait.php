<?php

declare(strict_types=1);

namespace Qameta\Allure\Model;

trait ModelProviderTrait
{
    /**
     * @return list<Link>
     */
    public function getLinks(): array
    {
        return [];
    }

    /**
     * @return list<Label>
     */
    public function getLabels(): array
    {
        return [];
    }

    /**
     * @return list<Parameter>
     */
    public function getParameters(): array
    {
        return [];
    }

    public function getDisplayName(): ?string
    {
        return null;
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function getDescriptionHtml(): ?string
    {
        return null;
    }

    public function getFullName(): ?string
    {
        return null;
    }
}
