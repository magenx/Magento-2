<?php

declare(strict_types=1);

namespace Qameta\Allure\Model;

use DateTimeImmutable;

interface StorableResultInterface extends ResultInterface
{
    public function getName(): ?string;

    public function setName(?string $name): static;

    public function getDescription(): ?string;

    public function setDescription(?string $description): static;

    public function getDescriptionHtml(): ?string;

    public function setDescriptionHtml(?string $descriptionHtml): static;

    public function getStart(): ?DateTimeImmutable;

    public function setStart(?DateTimeImmutable $start): static;

    public function getStop(): ?DateTimeImmutable;

    public function setStop(?DateTimeImmutable $stop): static;
}
