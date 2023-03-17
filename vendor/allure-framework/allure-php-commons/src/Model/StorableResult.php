<?php

declare(strict_types=1);

namespace Qameta\Allure\Model;

use DateTimeImmutable;

abstract class StorableResult extends Result implements StorableResultInterface
{
    protected ?string $name = null;

    protected ?string $description = null;

    protected ?string $descriptionHtml = null;

    protected ?SerializableDate $start = null;

    protected ?SerializableDate $stop = null;

    final public function getName(): ?string
    {
        return $this->name;
    }

    final public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    final public function getDescription(): ?string
    {
        return $this->description;
    }

    final public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    final public function getDescriptionHtml(): ?string
    {
        return $this->descriptionHtml;
    }

    final public function setDescriptionHtml(?string $descriptionHtml): static
    {
        $this->descriptionHtml = $descriptionHtml;

        return $this;
    }

    final public function getStart(): ?DateTimeImmutable
    {
        return isset($this->start) ? $this->start->getDate() : null;
    }

    final public function setStart(?DateTimeImmutable $start): static
    {
        $this->start = isset($start) ? new SerializableDate($start) : null;

        return $this;
    }

    final public function getStop(): ?DateTimeImmutable
    {
        return isset($this->stop) ? $this->stop->getDate() : null;
    }

    final public function setStop(?DateTimeImmutable $stop): static
    {
        $this->stop = isset($stop) ? new SerializableDate($stop) : null;

        return $this;
    }
}
