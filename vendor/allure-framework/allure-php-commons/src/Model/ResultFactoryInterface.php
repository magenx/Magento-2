<?php

declare(strict_types=1);

namespace Qameta\Allure\Model;

interface ResultFactoryInterface
{
    public function createContainer(): ContainerResult;

    public function createTest(): TestResult;

    public function createStep(): StepResult;

    public function createFixture(): FixtureResult;

    public function createAttachment(): AttachmentResult;
}
