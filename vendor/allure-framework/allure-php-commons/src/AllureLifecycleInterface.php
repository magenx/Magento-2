<?php

declare(strict_types=1);

namespace Qameta\Allure;

use Qameta\Allure\Io\DataSourceInterface;
use Qameta\Allure\Model\AttachmentResult;
use Qameta\Allure\Model\ContainerResult;
use Qameta\Allure\Model\FixtureResult;
use Qameta\Allure\Model\StepResult;
use Qameta\Allure\Model\TestResult;

interface AllureLifecycleInterface
{
    public function switchThread(?string $thread): void;

    public function getCurrentTest(): ?string;

    public function getCurrentStep(): ?string;

    public function getCurrentTestOrStep(): ?string;

    public function startContainer(ContainerResult $container): void;

    public function updateContainer(callable $update, ?string $uuid = null): ?string;

    public function stopContainer(?string $uuid = null): ?string;

    public function writeContainer(string $uuid): void;

    public function startBeforeFixture(FixtureResult $fixture, ?string $containerUuid = null): void;

    public function startAfterFixture(FixtureResult $fixture, ?string $containerUuid = null): void;

    public function updateFixture(callable $update, ?string $uuid = null): ?string;

    public function stopFixture(?string $uuid = null): ?string;

    public function scheduleTest(TestResult $test, ?string $containerUuid = null): void;

    public function startTest(string $uuid): void;

    public function updateTest(callable $update, ?string $uuid = null): ?string;

    public function stopTest(?string $uuid = null): ?string;

    public function writeTest(string $uuid): void;

    public function startStep(StepResult $step, ?string $parentUuid = null): void;

    public function updateStep(callable $update, ?string $uuid = null): ?string;

    public function updateExecutionContext(callable $update, ?string $uuid = null): ?string;

    public function stopStep(?string $uuid = null): ?string;

    public function addAttachment(AttachmentResult $attachment, DataSourceInterface $data): void;
}
