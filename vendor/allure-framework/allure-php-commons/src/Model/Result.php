<?php

declare(strict_types=1);

namespace Qameta\Allure\Model;

abstract class Result implements ResultInterface
{
    use JsonSerializableTrait;

    private bool $excluded = false;

    public function __construct(
        protected string $uuid,
    ) {
    }

    final public function getUuid(): string
    {
        return $this->uuid;
    }

    final public function getExcluded(): bool
    {
        return $this->excluded;
    }

    final public function setExcluded(bool $excluded = true): static
    {
        $this->excluded = $excluded;

        return $this;
    }

    /**
     * @return list<string>
     */
    protected function excludeFromSerialization(): array
    {
        return ['excluded'];
    }
}
