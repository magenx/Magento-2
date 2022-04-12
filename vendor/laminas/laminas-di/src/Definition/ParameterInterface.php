<?php

declare(strict_types=1);

namespace Laminas\Di\Definition;

/**
 * Parameter definition
 */
interface ParameterInterface
{
    public function getName(): string;

    public function getPosition(): int;

    public function getType(): ?string;

    /**
     * @return mixed
     */
    public function getDefault();

    public function isRequired(): bool;

    public function isBuiltin(): bool;
}
