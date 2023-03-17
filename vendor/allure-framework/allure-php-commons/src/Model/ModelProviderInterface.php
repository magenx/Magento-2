<?php

declare(strict_types=1);

namespace Qameta\Allure\Model;

interface ModelProviderInterface
{
    /**
     * @return list<Link>
     */
    public function getLinks(): array;

    /**
     * @return list<Label>
     */
    public function getLabels(): array;

    /**
     * @return list<Parameter>
     */
    public function getParameters(): array;

    public function getDisplayName(): ?string;

    public function getDescription(): ?string;

    public function getDescriptionHtml(): ?string;

    public function getFullName(): ?string;
}
