<?php

declare(strict_types=1);

namespace Qameta\Allure\Internal;

use Qameta\Allure\Internal\Exception\StorableNotFoundException;
use Qameta\Allure\Model\ContainerResult;
use Qameta\Allure\Model\ExecutionContextInterface;
use Qameta\Allure\Model\FixtureResult;
use Qameta\Allure\Model\ResultType;
use Qameta\Allure\Model\StepResult;
use Qameta\Allure\Model\StorableResultInterface;
use Qameta\Allure\Model\TestResult;

/**
 * @internal
 */
class ResultStorage implements ResultStorageInterface
{
    /**
     * @var array<string, StorableResultInterface>
     */
    private array $storage = [];

    public function set(StorableResultInterface $object): void
    {
        $this->storage[$object->getUuid()] = $object;
    }

    public function unset(string $uuid): void
    {
        unset($this->storage[$uuid]);
    }

    public function getContainer(string $uuid): ContainerResult
    {
        return $this->findObject(ContainerResult::class, $uuid)
            ?? throw new StorableNotFoundException(ResultType::container(), $uuid);
    }

    public function getFixture(string $uuid): FixtureResult
    {
        return $this->findObject(FixtureResult::class, $uuid)
            ?? throw new StorableNotFoundException(ResultType::fixture(), $uuid);
    }

    public function getTest(string $uuid): TestResult
    {
        return $this->findObject(TestResult::class, $uuid)
            ?? throw new StorableNotFoundException(ResultType::test(), $uuid);
    }

    public function getStep(string $uuid): StepResult
    {
        return $this->findObject(StepResult::class, $uuid)
            ?? throw new StorableNotFoundException(ResultType::step(), $uuid);
    }

    public function getExecutionContext(string $uuid): ExecutionContextInterface
    {
        return $this->findObject(ExecutionContextInterface::class, $uuid)
            ?? throw new StorableNotFoundException(ResultType::executableContext(), $uuid);
    }

    /**
     * @template T of StorableResultInterface
     * @param class-string<T> $class
     * @param string $uuid
     * @return T|null
     */
    private function findObject(string $class, string $uuid): ?StorableResultInterface
    {
        $object = $this->storage[$uuid] ?? null;

        return $object instanceof $class ? $object : null;
    }
}
