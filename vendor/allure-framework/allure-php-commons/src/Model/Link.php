<?php

namespace Qameta\Allure\Model;

use JsonSerializable;

final class Link implements JsonSerializable
{
    use JsonSerializableTrait;

    public function __construct(
        private ?string $name = null,
        private ?string $url = null,
        private ?LinkType $type = null,
    ) {
    }

    public static function issue(string $name, ?string $url): self
    {
        return new self(
            name: $name,
            url: $url,
            type: LinkType::issue(),
        );
    }

    public static function tms(string $name, ?string $url): self
    {
        return new self(
            name: $name,
            url: $url,
            type: LinkType::tms(),
        );
    }

    public static function custom(string $name, ?string $url): self
    {
        return new self(
            name: $name,
            url: $url,
            type: LinkType::custom(),
        );
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

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getType(): ?LinkType
    {
        return $this->type;
    }

    public function setType(?LinkType $type): self
    {
        $this->type = $type;

        return $this;
    }
}
