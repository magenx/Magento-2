<?php

declare(strict_types=1);

namespace Qameta\Allure\Internal\Exception;

use LogicException;
use Qameta\Allure\Model\ResultType;
use Throwable;

final class StorableNotFoundException extends LogicException
{
    public function __construct(
        private ResultType $resultType,
        private string $uuid,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            $this->buildMessage(),
            0,
            $previous,
        );
    }

    private function buildMessage(): string
    {
        $resultName = match ($this->resultType) {
            ResultType::attachment() => 'Attachment',
            ResultType::container() => 'Container',
            ResultType::test() => 'Test',
            ResultType::fixture() => 'Fixture',
            ResultType::step() => 'Step',
            ResultType::executableContext() => 'Executable context',
            default => '<Unknown result>',
        };

        return "{$resultName} with UUID {$this->uuid} is not found";
    }

    final public function getUuid(): string
    {
        return $this->uuid;
    }
}
