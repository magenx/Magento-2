<?php

declare(strict_types=1);

namespace Qameta\Allure\Model;

final class AttachmentResult extends Result
{
    protected ?string $name = null;

    protected ?string $source = null;

    protected ?string $type = null;

    protected ?string $fileExtension = null;

    public function getResultType(): ResultType
    {
        return ResultType::attachment();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getFileExtension(): ?string
    {
        return $this->fileExtension;
    }

    public function setFileExtension(?string $fileExtension): self
    {
        $this->fileExtension = $fileExtension;

        return $this;
    }

    protected function excludeFromSerialization(): array
    {
        return ['uuid', 'fileExtension', ...parent::excludeFromSerialization()];
    }

    public function getNestedResults(): array
    {
        return [];
    }
}
