<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Model;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Model\ResultFactory;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactoryInterface;

/**
 * @covers \Qameta\Allure\Model\ResultFactory
 */
class ResultFactoryTest extends TestCase
{
    public function testCreateContainer_FactoryProvidesUuid_ResultHasSameUuid(): void
    {
        $uuidFactory = $this->createStub(UuidFactoryInterface::class);
        $uuid = Uuid::fromString('00000000-0000-0000-0000-000000000001');
        $uuidFactory
            ->method('uuid4')
            ->willReturn($uuid);

        $resultFactory = new ResultFactory($uuidFactory);
        self::assertSame(
            '00000000-0000-0000-0000-000000000001',
            $resultFactory->createContainer()->getUuid(),
        );
    }

    public function testCreateTest_FactoryProvidesUuid_ResultHasSameUuid(): void
    {
        $uuidFactory = $this->createStub(UuidFactoryInterface::class);
        $uuid = Uuid::fromString('00000000-0000-0000-0000-000000000001');
        $uuidFactory
            ->method('uuid4')
            ->willReturn($uuid);

        $resultFactory = new ResultFactory($uuidFactory);
        self::assertSame(
            '00000000-0000-0000-0000-000000000001',
            $resultFactory->createTest()->getUuid(),
        );
    }

    public function testCreateStep_FactoryProvidesUuid_ResultHasSameUuid(): void
    {
        $uuidFactory = $this->createStub(UuidFactoryInterface::class);
        $uuid = Uuid::fromString('00000000-0000-0000-0000-000000000001');
        $uuidFactory
            ->method('uuid4')
            ->willReturn($uuid);

        $resultFactory = new ResultFactory($uuidFactory);
        self::assertSame(
            '00000000-0000-0000-0000-000000000001',
            $resultFactory->createStep()->getUuid(),
        );
    }

    public function testCreateFixture_FactoryProvidesUuid_ResultHasSameUuid(): void
    {
        $uuidFactory = $this->createStub(UuidFactoryInterface::class);
        $uuid = Uuid::fromString('00000000-0000-0000-0000-000000000001');
        $uuidFactory
            ->method('uuid4')
            ->willReturn($uuid);

        $resultFactory = new ResultFactory($uuidFactory);
        self::assertSame(
            '00000000-0000-0000-0000-000000000001',
            $resultFactory->createFixture()->getUuid(),
        );
    }

    public function testCreateAttachment_FactoryProvidesUuid_ResultHasSameUuid(): void
    {
        $uuidFactory = $this->createStub(UuidFactoryInterface::class);
        $uuid = Uuid::fromString('00000000-0000-0000-0000-000000000001');
        $uuidFactory
            ->method('uuid4')
            ->willReturn($uuid);

        $resultFactory = new ResultFactory($uuidFactory);
        self::assertSame(
            '00000000-0000-0000-0000-000000000001',
            $resultFactory->createAttachment()->getUuid(),
        );
    }
}
