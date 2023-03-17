<?php

declare(strict_types=1);

namespace Qameta\Allure\Model;

use function array_map;
use function array_merge;
use function array_values;

final class ModelProviderChain implements ModelProviderInterface
{
    /**
     * @var list<ModelProviderInterface>
     */
    private array $providers;

    public function __construct(ModelProviderInterface ...$providers)
    {
        $this->providers = array_values($providers);
    }

    /**
     * @return list<Link>
     */
    public function getLinks(): array
    {
        return array_merge(
            ...array_map(
                /** @psalm-return list<Link> */
                fn (ModelProviderInterface $p): array => $p->getLinks(),
                $this->providers,
            ),
        );
    }

    /**
     * @return list<Label>
     */
    public function getLabels(): array
    {
        return array_merge(
            ...array_map(
                /** @psalm-return list<Label> */
                fn (ModelProviderInterface $p): array => $p->getLabels(),
                $this->providers,
            ),
        );
    }

    /**
     * @return list<Parameter>
     */
    public function getParameters(): array
    {
        return array_merge(
            ...array_map(
                /** @psalm-return list<Parameter> */
                fn (ModelProviderInterface $p): array => $p->getParameters(),
                $this->providers,
            ),
        );
    }

    public function getDisplayName(): ?string
    {
        $displayName = null;
        foreach ($this->providers as $provider) {
            $displayName ??= $provider->getDisplayName();
        }

        return $displayName;
    }

    public function getDescription(): ?string
    {
        $description = null;
        foreach ($this->providers as $provider) {
            $description ??= $provider->getDescription();
        }

        return $description;
    }

    public function getDescriptionHtml(): ?string
    {
        $descriptionHtml = null;
        foreach ($this->providers as $provider) {
            $descriptionHtml ??= $provider->getDescriptionHtml();
        }

        return $descriptionHtml;
    }

    public function getFullName(): ?string
    {
        $fullName = null;
        foreach ($this->providers as $provider) {
            $fullName ??= $provider->getFullName();
        }

        return $fullName;
    }
}
