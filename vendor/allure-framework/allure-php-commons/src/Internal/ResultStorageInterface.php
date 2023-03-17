<?php

declare(strict_types=1);

namespace Qameta\Allure\Internal;

use Qameta\Allure\Model\ContainerResult;
use Qameta\Allure\Model\ExecutionContextInterface;
use Qameta\Allure\Model\FixtureResult;
use Qameta\Allure\Model\StepResult;
use Qameta\Allure\Model\StorableResultInterface;
use Qameta\Allure\Model\TestResult;

/**
 * @internal
 */
interface ResultStorageInterface
{
    public function set(StorableResultInterface $object): void;

    public function unset(string $uuid): void;

    public function getContainer(string $uuid): ContainerResult;

    public function getFixture(string $uuid): FixtureResult;

    public function getTest(string $uuid): TestResult;

    public function getStep(string $uuid): StepResult;

    public function getExecutionContext(string $uuid): ExecutionContextInterface;
}
