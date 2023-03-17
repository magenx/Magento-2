<?php

declare(strict_types=1);

namespace Qameta\Allure\Model;

use Ramsey\Uuid\UuidFactoryInterface;

final class ResultFactory implements ResultFactoryInterface
{
    public function __construct(private UuidFactoryInterface $uuidFactory)
    {
    }

    public function createContainer(): ContainerResult
    {
        return new ContainerResult($this->createUuid());
    }

    public function createTest(): TestResult
    {
        return new TestResult($this->createUuid());
    }

    public function createStep(): StepResult
    {
        return new StepResult($this->createUuid());
    }

    public function createFixture(): FixtureResult
    {
        return new FixtureResult($this->createUuid());
    }

    public function createAttachment(): AttachmentResult
    {
        return new AttachmentResult($this->createUuid());
    }

    private function createUuid(): string
    {
        return $this
            ->uuidFactory
            ->uuid4()
            ->toString();
    }
}
