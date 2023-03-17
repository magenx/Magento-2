<?php

declare(strict_types=1);

namespace Qameta\Allure\Model;

use JsonSerializable;

final class StatusDetails implements JsonSerializable
{
    use JsonSerializableTrait;

    public function __construct(
        protected ?bool $known = null,
        protected ?bool $muted = null,
        protected ?bool $flaky = null,
        protected ?string $message = null,
        protected ?string $trace = null,
    ) {
    }

    public function isKnown(): ?bool
    {
        return $this->known;
    }

    public function makeKnown(?bool $known): self
    {
        $this->known = $known;

        return $this;
    }

    public function isMuted(): ?bool
    {
        return $this->muted;
    }

    public function makeMuted(?bool $muted): self
    {
        $this->muted = $muted;

        return $this;
    }

    public function isFlaky(): ?bool
    {
        return $this->flaky;
    }

    public function makeFlaky(?bool $flaky): self
    {
        $this->flaky = $flaky;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getTrace(): ?string
    {
        return $this->trace;
    }

    public function setTrace(?string $trace): self
    {
        $this->trace = $trace;

        return $this;
    }
}
