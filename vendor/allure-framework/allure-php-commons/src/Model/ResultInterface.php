<?php

declare(strict_types=1);

namespace Qameta\Allure\Model;

use JsonSerializable;

interface ResultInterface extends JsonSerializable
{
    public function getUuid(): string;

    public function getResultType(): ResultType;

    public function getExcluded(): bool;

    public function setExcluded(bool $excluded = true): static;

    /**
     * @return list<ResultInterface>
     */
    public function getNestedResults(): array;
}
