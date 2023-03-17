<?php

declare(strict_types=1);

namespace Qameta\Allure\Model;

final class FixtureResult extends ExecutionContext
{
    public function getResultType(): ResultType
    {
        return ResultType::fixture();
    }

    /**
     * @return list<string>
     */
    protected function excludeFromSerialization(): array
    {
        return ['uuid', ...parent::excludeFromSerialization()];
    }
}
