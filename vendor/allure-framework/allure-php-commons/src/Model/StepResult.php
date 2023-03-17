<?php

namespace Qameta\Allure\Model;

final class StepResult extends ExecutionContext
{
    public function getResultType(): ResultType
    {
        return ResultType::step();
    }

    /**
     * @return list<string>
     */
    protected function excludeFromSerialization(): array
    {
        return ['uuid', ...parent::excludeFromSerialization()];
    }
}
