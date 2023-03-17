<?php

declare(strict_types=1);

namespace Qameta\Allure\Model;

use DateTimeImmutable;
use JsonSerializable;

final class SerializableDate implements JsonSerializable
{
    public function __construct(private DateTimeImmutable $date)
    {
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function jsonSerialize(): int
    {
        return $this->date->getTimestamp() * 1000 + (int) $this->date->format('v');
    }
}
