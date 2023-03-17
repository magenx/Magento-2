<?php

declare(strict_types=1);

namespace Qameta\Allure\Test;

use DateTimeImmutable;
use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Qameta\Allure\AllureLifecycle;
use Qameta\Allure\Exception\ActiveContainerNotFoundException;
use Qameta\Allure\Exception\ActiveExecutionContextNotFoundException;
use Qameta\Allure\Exception\ActiveStepNotFoundException;
use Qameta\Allure\Exception\ActiveTestNotFoundException;
use Qameta\Allure\Exception\InvalidExecutionContextException;
use Qameta\Allure\Internal\HooksNotifierInterface;
use Qameta\Allure\Internal\ResultStorageInterface;
use Qameta\Allure\Internal\ThreadContext;
use Qameta\Allure\Internal\ThreadContextInterface;
use Qameta\Allure\Io\ClockInterface;
use Qameta\Allure\Io\DataSourceInterface;
use Qameta\Allure\Io\ResultsWriterInterface;
use Qameta\Allure\Model\AttachmentResult;
use Qameta\Allure\Model\ContainerResult;
use Qameta\Allure\Model\ExecutionContextInterface;
use Qameta\Allure\Model\FixtureResult;
use Qameta\Allure\Model\Stage;
use Qameta\Allure\Model\StepResult;
use Qameta\Allure\Model\StorableResultInterface;
use Qameta\Allure\Model\TestResult;
use Throwable;

/**
 * @covers \Qameta\Allure\AllureLifecycle
 */
class AllureLifecycleTest extends TestCase
{
    /**
     * @dataProvider providerSwitchThread
     */
    public function testSwitchThread_GivenThread_SwitcherToSameThreadInContext(?string $thread): void
    {
        $threadContext = $this->createMock(ThreadContextInterface::class);
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStub(ResultStorageInterface::class),
            $threadContext,
        );
        $threadContext
            ->expects(self::once())
            ->method('switchThread')
            ->with(self::identicalTo($thread));
        $lifecycle->switchThread($thread);
    }

    /**
     * @dataProvider providerGetCurrentTest
     */
    public function testGetCurrentTest_Constructed_ReturnsCurrentTestFromContext(?string $currentTest): void
    {
        $threadContext = $this->createStub(ThreadContextInterface::class);
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStub(ResultStorageInterface::class),
            $threadContext,
        );
        $threadContext
            ->method('getCurrentTest')
            ->willReturn($currentTest);
        self::assertSame($currentTest, $lifecycle->getCurrentTest());
    }

    /**
     * @return iterable<string, array{string|null}>
     */
    public static function providerGetCurrentTest(): iterable
    {
        return [
            'No current test' => [null],
            'Current test exists' => ['a'],
        ];
    }

    /**
     * @dataProvider providerGetCurrentStep
     */
    public function testGetCurrentStep_Constructed_ReturnsCurrentStepFromContext(?string $currentTest): void
    {
        $threadContext = $this->createStub(ThreadContextInterface::class);
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStub(ResultStorageInterface::class),
            $threadContext,
        );
        $threadContext
            ->method('getCurrentStep')
            ->willReturn($currentTest);
        self::assertSame($currentTest, $lifecycle->getCurrentStep());
    }

    /**
     * @return iterable<string, array{string|null}>
     */
    public static function providerGetCurrentStep(): iterable
    {
        return [
            'No current step' => [null],
            'Current step exists' => ['a'],
        ];
    }

    /**
     * @dataProvider providerGetCurrentTestOrStep
     */
    public function testGetCurrentTestOrStep_Constructed_ReturnsCurrentTestOrStepFromContext(?string $currentTest): void
    {
        $threadContext = $this->createStub(ThreadContextInterface::class);
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStub(ResultStorageInterface::class),
            $threadContext,
        );
        $threadContext
            ->method('getCurrentTestOrStep')
            ->willReturn($currentTest);
        self::assertSame($currentTest, $lifecycle->getCurrentTestOrStep());
    }

    /**
     * @return iterable<string, array{string|null}>
     */
    public static function providerGetCurrentTestOrStep(): iterable
    {
        return [
            'No current test or step' => [null],
            'Current test or step exists' => ['a'],
        ];
    }

    /**
     * @return iterable<string, array{string|null}>
     */
    public static function providerSwitchThread(): iterable
    {
        return [
            'Default thread' => [null],
            'Custom thread' => ['a'],
        ];
    }

    public function testStartContainer_NoExceptionsThrownDuringStart_NotifiesHooksWithoutError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStub(ResultStorageInterface::class),
            new ThreadContext(),
        );

        $container = new ContainerResult('a');
        $hooksNotifier
            ->expects(self::once())
            ->method('beforeContainerStart')
            ->id('before')
            ->with(self::identicalTo($container));
        $hooksNotifier
            ->expects(self::never())
            ->method('onLifecycleError');
        $hooksNotifier
            ->expects(self::once())
            ->after('before')
            ->method('afterContainerStart')
            ->with(self::identicalTo($container));
        $lifecycle->startContainer($container);
    }

    public function testStartContainer_ExceptionThrownDuringStart_NotifiesHooksWithError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $error = new Exception();
        $container = new ContainerResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createNonSettableStorage($container, $error),
            new ThreadContext(),
        );

        $hooksNotifier
            ->expects(self::once())
            ->id('before')
            ->method('beforeContainerStart')
            ->with(self::identicalTo($container));
        $hooksNotifier
            ->expects(self::once())
            ->id('error')
            ->after('before')
            ->method('onLifecycleError')
            ->with(self::identicalTo($error));
        $hooksNotifier
            ->expects(self::once())
            ->after('error')
            ->method('afterContainerStart')
            ->with(self::identicalTo($container));
        $lifecycle->startContainer($container);
    }

    public function testStartContainer_NoExceptionsThrownDuringStart_NeverLogsError(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStub(ResultStorageInterface::class),
            new ThreadContext(),
        );

        $logger
            ->expects(self::never())
            ->method('error');
        $lifecycle->startContainer(new ContainerResult('a'));
    }

    public function testStartContainer_StorageFailsToSetContainer_LogsError(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $error = new Exception();
        $container = new ContainerResult('a');
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createNonSettableStorage($container, $error),
            new ThreadContext(),
        );

        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Container (UUID: {uuid}) not started'),
                self::identicalTo(['uuid' => 'a', 'exception' => $error]),
            );
        $lifecycle->startContainer($container);
    }

    public function testStartContainer_ClockProvidesTime_ContainerStartIsSetToSameTime(): void
    {
        $time = new DateTimeImmutable('@0');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock($time),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStub(ResultStorageInterface::class),
            new ThreadContext(),
        );

        $container = new ContainerResult('a');
        $lifecycle->startContainer($container);
        self::assertSame($time, $container->getStart());
    }

    public function testStartContainer_GivenContainer_SetsSameContainerInStorage(): void
    {
        $resultStorage = $this->createMock(ResultStorageInterface::class);
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $resultStorage,
            new ThreadContext(),
        );

        $container = new ContainerResult('a');
        $resultStorage
            ->expects(self::once())
            ->method('set')
            ->with(self::identicalTo($container));
        $lifecycle->startContainer($container);
    }

    public function testUpdateContainer_ContainerNeitherGivenNorStarted_LogsErrorAndReturnsNull(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStub(ResultStorageInterface::class),
            new ThreadContext(),
        );

        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Container (UUID: {uuid}) not updated'),
                self::equalTo(['uuid' => null, 'exception' => new ActiveContainerNotFoundException()]),
            );
        self::assertNull($lifecycle->updateContainer(fn () => null));
    }

    public function testUpdateContainer_StorageFailsToProvideGivenContainer_LogsErrorAndReturnsNull(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $error = new Exception();
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithoutContainer('a', $error),
            new ThreadContext(),
        );

        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Container (UUID: {uuid}) not updated'),
                self::equalTo(['uuid' => 'a', 'exception' => $error]),
            );
        self::assertNull($lifecycle->updateContainer(fn () => null, 'a'));
    }

    public function testUpdateContainer_StorageFailsToProvideStartedContainer_LogsErrorAndReturnsNull(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $error = new Exception();
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithoutContainer('a', $error),
            new ThreadContext(),
        );

        $lifecycle->startContainer(new ContainerResult('a'));

        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Container (UUID: {uuid}) not updated'),
                self::equalTo(['uuid' => 'a', 'exception' => $error]),
            );
        self::assertNull($lifecycle->updateContainer(fn () => null));
    }

    public function testUpdateContainer_ContainerNotGivenButStarted_NeverLogsErrorAndReturnsUuid(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $container = new ContainerResult('a');
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithContainer($container),
            new ThreadContext(),
        );

        $lifecycle->startContainer($container);

        $logger
            ->expects(self::never())
            ->method('error');
        self::assertSame('a', $lifecycle->updateContainer(fn () => null));
    }

    public function testUpdateContainer_StorageProvidesGivenContainer_NeverLogsErrorAndReturnsUuid(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithContainer(new ContainerResult('a')),
            new ThreadContext(),
        );

        $logger
            ->expects(self::never())
            ->method('error');
        self::assertSame('a', $lifecycle->updateContainer(fn () => null, 'a'));
    }

    public function testUpdateContainer_NoExceptionThrownDuringUpdate_NotifiesHooksWithoutError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $container = new ContainerResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStorageWithContainer($container),
            new ThreadContext(),
        );

        $hooksNotifier
            ->expects(self::once())
            ->id('before')
            ->method('beforeContainerUpdate')
            ->with(self::identicalTo($container));
        $hooksNotifier
            ->expects(self::never())
            ->method('onLifecycleError');
        $hooksNotifier
            ->expects(self::once())
            ->after('before')
            ->method('afterContainerUpdate')
            ->with(self::identicalTo($container));
        $lifecycle->updateContainer(fn () => null, 'a');
    }

    public function testUpdateContainer_ExceptionThrownDuringUpdate_NotifiesHooksWithError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $container = new ContainerResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStorageWithContainer($container),
            new ThreadContext(),
        );

        $error = new Exception();
        $hooksNotifier
            ->expects(self::once())
            ->id('before')
            ->method('beforeContainerUpdate')
            ->with(self::identicalTo($container));
        $hooksNotifier
            ->expects(self::once())
            ->id('error')
            ->after('before')
            ->method('onLifecycleError')
            ->with(self::identicalTo($error));
        $hooksNotifier
            ->expects(self::once())
            ->after('error')
            ->method('afterContainerUpdate')
            ->with(self::identicalTo($container));
        $lifecycle->updateContainer(fn () => throw $error, 'a');
    }

    public function testUpdateContainer_CallbackThrowsException_LogsErrorAndReturnsUuid(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $container = new ContainerResult('a');
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithContainer($container),
            new ThreadContext(),
        );
        $error = new Exception();
        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Container (UUID: {uuid}) not updated'),
                self::identicalTo(['uuid' => 'a', 'exception' => $error]),
            );
        self::assertSame('a', $lifecycle->updateContainer(fn () => throw $error, 'a'));
    }

    public function testUpdateContainer_StorageProvidesContainer_SameContainerPassedToCallback(): void
    {
        $container = new ContainerResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithContainer($container),
            new ThreadContext(),
        );

        $lifecycle->updateContainer(fn (ContainerResult $c) => $c->setName('b'), 'a');
        self::assertSame('b', $container->getName());
    }

    public function testStopContainer_ContainerNeitherGivenNorStarted_LogsErrorAndReturnsNull(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStub(ResultStorageInterface::class),
            new ThreadContext(),
        );

        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Container (UUID: {uuid}) not stopped'),
                self::equalTo(['uuid' => null, 'exception' => new ActiveContainerNotFoundException()]),
            );
        self::assertNull($lifecycle->stopContainer());
    }

    public function testStopContainer_StorageFailsToProvideGivenContainer_LogsErrorAndReturnsNull(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $error = new Exception();
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithoutContainer('a', $error),
            new ThreadContext(),
        );

        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Container (UUID: {uuid}) not stopped'),
                self::equalTo(['uuid' => 'a', 'exception' => $error]),
            );
        self::assertNull($lifecycle->stopContainer('a'));
    }

    public function testStopContainer_StorageFailsToProvideStartedContainer_LogsErrorAndReturnsNull(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $error = new Exception();
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithoutContainer('a', $error),
            new ThreadContext(),
        );

        $lifecycle->startContainer(new ContainerResult('a'));

        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Container (UUID: {uuid}) not stopped'),
                self::equalTo(['uuid' => 'a', 'exception' => $error]),
            );
        self::assertNull($lifecycle->stopContainer());
    }

    public function testStopContainer_ContainerNotGivenButStarted_NeverLogsErrorAndReturnsUuid(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $container = new ContainerResult('a');
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithContainer($container),
            new ThreadContext(),
        );

        $lifecycle->startContainer($container);
        $logger
            ->expects(self::never())
            ->method('error');
        self::assertSame('a', $lifecycle->stopContainer());
    }

    public function testStopContainer_StorageProvidesGivenContainer_NeverLogsErrorAndReturnsUuid(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithContainer(new ContainerResult('a')),
            new ThreadContext(),
        );
        $logger
            ->expects(self::never())
            ->method('error');
        self::assertSame('a', $lifecycle->stopContainer('a'));
    }

    public function testStopContainer_ClockProvidesTime_ContainerHasSameStop(): void
    {
        $time = new DateTimeImmutable('@0');
        $container = new ContainerResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock($time),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithContainer($container),
            new ThreadContext(),
        );

        $lifecycle->stopContainer('a');
        self::assertSame($time, $container->getStop());
    }

    public function testStopContainer_NoExceptionThrownDuringStop_NotifiesHooksWithoutError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $container = new ContainerResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStorageWithContainer($container),
            new ThreadContext(),
        );

        $hooksNotifier
            ->expects(self::once())
            ->id('before')
            ->method('beforeContainerStop')
            ->with(self::identicalTo($container));
        $hooksNotifier
            ->expects(self::never())
            ->method('onLifecycleError');
        $hooksNotifier
            ->expects(self::once())
            ->after('before')
            ->method('afterContainerStop')
            ->with(self::identicalTo($container));
        $lifecycle->stopContainer('a');
    }

    public function testStopContainer_ExceptionThrownDuringStop_NotifiesHooksWithError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $error = new Exception();
        $container = new ContainerResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createFailingClock($error),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStorageWithContainer($container),
            new ThreadContext(),
        );
        $hooksNotifier
            ->expects(self::once())
            ->id('before')
            ->method('beforeContainerStop')
            ->with(self::identicalTo($container));
        $hooksNotifier
            ->expects(self::once())
            ->id('error')
            ->after('before')
            ->method('onLifecycleError')
            ->with(self::identicalTo($error));
        $hooksNotifier
            ->expects(self::once())
            ->after('error')
            ->method('afterContainerStop')
            ->with(self::identicalTo($container));
        $lifecycle->stopContainer('a');
    }

    public function testWriteContainer_StorageFailsToProvideContainer_LogsError(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $error = new Exception();
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithoutContainer('a', $error),
            new ThreadContext(),
        );
        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Container (UUID: {uuid}) not written'),
                self::identicalTo(['uuid' => 'a', 'exception' => $error]),
            );
        $lifecycle->writeContainer('a');
    }

    public function testWriteContainer_NoExceptionThrownDuringWrite_NotifiesHooksWithoutError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $container = new ContainerResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStorageWithContainer($container),
            new ThreadContext(),
        );
        $hooksNotifier
            ->expects(self::once())
            ->id('before')
            ->method('beforeContainerWrite')
            ->with(self::identicalTo($container));
        $hooksNotifier
            ->expects(self::never())
            ->method('onLifecycleError');
        $hooksNotifier
            ->expects(self::once())
            ->after('before')
            ->method('afterContainerWrite')
            ->with(self::identicalTo($container));
        $lifecycle->writeContainer('a');
    }

    public function testWriteContainer_ExceptionThrownDuringWrite_NotifiesHooksWithError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $container = new ContainerResult('a');
        $error = new Exception();
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStorageWithContainer($container, unsetError: $error),
            new ThreadContext(),
        );
        $hooksNotifier
            ->expects(self::once())
            ->id('before')
            ->method('beforeContainerWrite')
            ->with(self::identicalTo($container));
        $hooksNotifier
            ->expects(self::once())
            ->id('error')
            ->after('before')
            ->method('onLifecycleError')
            ->with(self::identicalTo($error));
        $hooksNotifier
            ->expects(self::once())
            ->after('error')
            ->method('afterContainerWrite')
            ->with(self::identicalTo($container));
        $lifecycle->writeContainer('a');
    }

    public function testWriteContainer_ExceptionThrownDuringWrite_LogsError(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $container = new ContainerResult('a');
        $error = new Exception();
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithContainer($container, unsetError: $error),
            new ThreadContext(),
        );
        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Container (UUID: {uuid}) not written'),
                self::identicalTo(['uuid' => 'a', 'exception' => $error]),
            );
        $lifecycle->writeContainer('a');
    }

    public function testWriteContainer_ContainerWithGivenUuid_StorageUnsetsResultWithSameUuid(): void
    {
        $container = new ContainerResult('a');
        $resultStorage = $this->createMock(ResultStorageInterface::class);
        $resultStorage
            ->method('getContainer')
            ->willReturn($container);
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $resultStorage,
            new ThreadContext(),
        );

        $resultStorage
            ->expects(self::once())
            ->method('unset')
            ->with(self::identicalTo('a'));
        $lifecycle->writeContainer('a');
    }

    public function testWriteContainer_ExcludedContainerWithNestedResults_RemovesNestedResults(): void
    {
        $resultsWriter = $this->createMock(ResultsWriterInterface::class);

        $container = new ContainerResult('a');

        $setUp = new FixtureResult('b');
        $setUpAttachment = new AttachmentResult('c');
        $setUp->addAttachments($setUpAttachment);
        $setUpStep = new StepResult('d');
        $setUpStepAttachment = new AttachmentResult('e');
        $setUpStep->addAttachments($setUpStepAttachment);
        $setUp->addSteps($setUpStep);
        $container->addBefores($setUp);

        $test = new TestResult('f');
        $testAttachment = new AttachmentResult('g');
        $test->addAttachments($testAttachment);
        $testStep = new StepResult('h');
        $testStepAttachment = new AttachmentResult('i');
        $testStep->addAttachments($testStepAttachment);
        $test->addSteps($testStep);
        $container->addChildren($test);

        $tearDown = new FixtureResult('j');
        $tearDownAttachment = new AttachmentResult('k');
        $tearDown->addAttachments($tearDownAttachment);
        $tearDownStep = new StepResult('l');
        $tearDownStepAttachment = new AttachmentResult('m');
        $tearDownStep->addAttachments($tearDownStepAttachment);
        $tearDown->addSteps($tearDownStep);
        $container->addAfters($tearDown);

        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $resultsWriter,
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithContainer($container),
            new ThreadContext(),
        );

        $container->setExcluded(true);
        $resultsWriter
            ->expects(self::exactly(6))
            ->method('removeAttachment')
            ->withConsecutive(
                [self::identicalTo($setUpAttachment)],
                [self::identicalTo($setUpStepAttachment)],
                [self::identicalTo($testAttachment)],
                [self::identicalTo($testStepAttachment)],
                [self::identicalTo($tearDownAttachment)],
                [self::identicalTo($tearDownStepAttachment)],
            );
        $resultsWriter
            ->expects(self::exactly(1))
            ->method('removeTest')
            ->withConsecutive(
                [self::identicalTo($test)],
            );
        $lifecycle->writeContainer('a');
    }

    public function testWriteContainer_WriterFailsToRemoveExcludedTest_LogsError(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $error = new Exception();
        $resultsWriter = $this->createStub(ResultsWriterInterface::class);
        $resultsWriter
            ->method('removeTest')
            ->willThrowException($error);

        $container = new ContainerResult('a');

        $test = new TestResult('b');
        $container->addChildren($test->setExcluded(true));

        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $resultsWriter,
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithContainer($container),
            new ThreadContext(),
        );

        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Test (UUID: {uuid}) not removed'),
                self::identicalTo(['uuid' => 'b', 'exception' => $error]),
            );
        $lifecycle->writeContainer('a');
    }

    public function testWriteContainer_WriterFailsToRemoveExcludedAttachment_LogsError(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $error = new Exception();
        $resultsWriter = $this->createStub(ResultsWriterInterface::class);
        $resultsWriter
            ->method('removeAttachment')
            ->willThrowException($error);

        $container = new ContainerResult('a');

        $test = new TestResult('b');
        $testAttachment = new AttachmentResult('c');
        $test->addAttachments($testAttachment->setExcluded(true));
        $container->addChildren($test);

        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $resultsWriter,
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithContainer($container),
            new ThreadContext(),
        );

        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Attachment (UUID: {uuid}) not removed'),
                self::identicalTo(['uuid' => 'c', 'exception' => $error]),
            );
        $lifecycle->writeContainer('a');
    }

    public function testWriteContainer_ContainerNotExcluded_WriterWritesContainer(): void
    {
        $resultsWriter = $this->createMock(ResultsWriterInterface::class);

        $container = new ContainerResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $resultsWriter,
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithContainer($container),
            new ThreadContext(),
        );

        $resultsWriter
            ->expects(self::once())
            ->method('writeContainer')
            ->with(self::identicalTo($container));
        $lifecycle->writeContainer('a');
    }

    public function testWriteContainer_ContainerExcluded_WriterNeverWritesContainer(): void
    {
        $resultsWriter = $this->createMock(ResultsWriterInterface::class);

        $container = new ContainerResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $resultsWriter,
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithContainer($container),
            new ThreadContext(),
        );

        $container->setExcluded(true);
        $resultsWriter
            ->expects(self::never())
            ->method('writeContainer');
        $lifecycle->writeContainer('a');
    }

    public function testStartBeforeFixture_ExceptionNotThrownDuringStart_NeverLogsError(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $container = new ContainerResult('a');
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithContainer($container),
            new ThreadContext(),
        );
        $logger
            ->expects(self::never())
            ->method('error');
        $lifecycle->startBeforeFixture(new FixtureResult('b'), 'a');
    }

    public function testStartBeforeFixture_ContainerNeitherGivenNorStarted_LogsError(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStub(ResultStorageInterface::class),
            new ThreadContext(),
        );
        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains(
                    'Set up fixture (UUID: {uuid}, container UUID: {containerUuid}) not started',
                ),
                self::equalTo(
                    ['uuid' => 'b', 'containerUuid' => null, 'exception' => new ActiveContainerNotFoundException()],
                ),
            );
        $lifecycle->startBeforeFixture(new FixtureResult('b'));
    }

    public function testStartBeforeFixture_ExceptionThrownAfterContainerIsProvided_LogsError(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $error = new Exception();
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createFailingClock($error),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithContainer(new ContainerResult('a')),
            new ThreadContext(),
        );
        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains(
                    'Set up fixture (UUID: {uuid}, container UUID: {containerUuid}) not started',
                ),
                self::equalTo(
                    ['uuid' => 'b', 'containerUuid' => 'a', 'exception' => $error],
                ),
            );
        $lifecycle->startBeforeFixture(new FixtureResult('b'), 'a');
    }

    public function testStartBeforeFixture_ContainerNotGivenButStarted_NeverLogsError(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $container = new ContainerResult('a');
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithContainer($container),
            new ThreadContext(),
        );
        $lifecycle->startContainer($container);
        $logger
            ->expects(self::never())
            ->method('error');
        $lifecycle->startBeforeFixture(new FixtureResult('b'));
    }

    public function testStartBeforeFixture_ContainerGiven_NeverLogsError(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithContainer(new ContainerResult('a')),
            new ThreadContext(),
        );

        $logger
            ->expects(self::never())
            ->method('error');
        $lifecycle->startBeforeFixture(new FixtureResult('b'), 'a');
    }

    public function testStartBeforeFixture_GivenFixture_ContainerContainsSameFixture(): void
    {
        $container = new ContainerResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithContainer($container),
            new ThreadContext(),
        );

        $fixture = new FixtureResult('b');
        $lifecycle->startBeforeFixture($fixture, 'a');
        self::assertSame([$fixture], $container->getBefores());
    }

    public function testStartBeforeFixture_ClockProvidesGivenTime_FixtureStartIsSameTime(): void
    {
        $time = new DateTimeImmutable('@0');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock($time),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithContainer(new ContainerResult('a')),
            new ThreadContext(),
        );

        $fixture = new FixtureResult('b');
        $lifecycle->startBeforeFixture($fixture, 'a');
        self::assertSame($time, $fixture->getStart());
    }

    public function testStartBeforeFixture_GivenFixture_FixtureOnRunningStage(): void
    {
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithContainer(new ContainerResult('a')),
            new ThreadContext(),
        );

        $fixture = new FixtureResult('b');
        $lifecycle->startBeforeFixture($fixture, 'a');
        self::assertSame(Stage::running(), $fixture->getStage());
    }

    public function testStartBeforeFixture_ThreadContextWithNonEmptyStack_OnlyFixtureInContextStack(): void
    {
        $threadContext = new ThreadContext();
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithContainer(new ContainerResult('a')),
            $threadContext,
        );

        $fixture = new FixtureResult('b');
        $threadContext->push('c');
        $threadContext->push('d');
        $lifecycle->startBeforeFixture($fixture, 'a');
        self::assertSame(['b'], $this->extractThreadStack($threadContext));
    }

    public function testStartAfterFixture_ExceptionNotThrownDuringStart_NeverLogsError(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $container = new ContainerResult('a');
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithContainer($container),
            new ThreadContext(),
        );
        $logger
            ->expects(self::never())
            ->method('error');
        $lifecycle->startAfterFixture(new FixtureResult('b'), 'a');
    }

    public function testStartAfterFixture_ContainerNeitherGivenNorStarted_LogsError(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStub(ResultStorageInterface::class),
            new ThreadContext(),
        );
        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains(
                    'Tear down fixture (UUID: {uuid}, container UUID: {containerUuid}) not started',
                ),
                self::equalTo(
                    ['uuid' => 'a', 'containerUuid' => null, 'exception' => new ActiveContainerNotFoundException()],
                ),
            );
        $lifecycle->startAfterFixture(new FixtureResult('a'));
    }

    public function testStartAfterFixture_ExceptionThrownAfterContainerIsProvided_LogsError(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $error = new Exception();
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createFailingClock($error),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithContainer(new ContainerResult('a')),
            new ThreadContext(),
        );
        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains(
                    'Tear down fixture (UUID: {uuid}, container UUID: {containerUuid}) not started',
                ),
                self::equalTo(
                    ['uuid' => 'b', 'containerUuid' => 'a', 'exception' => $error],
                ),
            );
        $lifecycle->startAfterFixture(new FixtureResult('b'), 'a');
    }

    public function testStartAfterFixture_ContainerNotGivenButStarted_NeverLogsError(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $container = new ContainerResult('a');
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithContainer($container),
            new ThreadContext(),
        );
        $lifecycle->startContainer($container);
        $logger
            ->expects(self::never())
            ->method('error');
        $lifecycle->startAfterFixture(new FixtureResult('b'));
    }

    public function testStartAfterFixture_ContainerGiven_NeverLogsError(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithContainer(new ContainerResult('a')),
            new ThreadContext(),
        );

        $logger
            ->expects(self::never())
            ->method('error');
        $lifecycle->startAfterFixture(new FixtureResult('b'), 'a');
    }

    public function testStartAfterFixture_GivenFixture_ContainerContainsSameFixture(): void
    {
        $container = new ContainerResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithContainer($container),
            new ThreadContext(),
        );

        $fixture = new FixtureResult('b');
        $lifecycle->startAfterFixture($fixture, 'a');
        self::assertSame([$fixture], $container->getAfters());
    }

    public function testStartAfterFixture_ClockProvidesGivenTime_FixtureStartIsSameTime(): void
    {
        $time = new DateTimeImmutable('@0');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock($time),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithContainer(new ContainerResult('a')),
            new ThreadContext(),
        );

        $fixture = new FixtureResult('b');
        $lifecycle->startAfterFixture($fixture, 'a');
        self::assertSame($time, $fixture->getStart());
    }

    public function testStartAfterFixture_GivenFixture_FixtureOnRunningStage(): void
    {
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithContainer(new ContainerResult('a')),
            new ThreadContext(),
        );

        $fixture = new FixtureResult('b');
        $lifecycle->startAfterFixture($fixture, 'a');
        self::assertSame(Stage::running(), $fixture->getStage());
    }

    public function testStartAfterFixture_ThreadContextWithNonEmptyStack_OnlyFixtureInContextStack(): void
    {
        $threadContext = new ThreadContext();
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithContainer(new ContainerResult('a')),
            $threadContext,
        );

        $fixture = new FixtureResult('b');
        $threadContext->push('c');
        $threadContext->push('d');
        $lifecycle->startAfterFixture($fixture, 'a');
        self::assertSame(['b'], $this->extractThreadStack($threadContext));
    }

    public function testUpdateFixture_FixtureNeitherGivenNorStarted_LogsErrorAndReturnsNull(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStub(ResultStorageInterface::class),
            new ThreadContext(),
        );

        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Fixture (UUID: {uuid}) not updated'),
                self::equalTo(['uuid' => null, 'exception' => new ActiveTestNotFoundException()]),
            );
        self::assertNull($lifecycle->updateFixture(fn () => null));
    }

    public function testUpdateFixture_StorageFailsToProvideGivenFixture_LogsErrorAndReturnsNull(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $error = new Exception();
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithoutFixture('a', $error),
            new ThreadContext(),
        );

        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Fixture (UUID: {uuid}) not updated'),
                self::identicalTo(['uuid' => 'a', 'exception' => $error]),
            );
        self::assertNull($lifecycle->updateFixture(fn () => null, 'a'));
    }

    public function testUpdateFixture_StorageFailsToProvideStartedFixture_LogsErrorAndReturnsNull(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $error = new Exception();
        $threadContext = new ThreadContext();
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithoutFixture('a', $error),
            $threadContext,
        );

        $threadContext->push('a');
        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Fixture (UUID: {uuid}) not updated'),
                self::identicalTo(['uuid' => 'a', 'exception' => $error]),
            );
        self::assertNull($lifecycle->updateFixture(fn () => null));
    }

    public function testUpdateFixture_StorageProvidesFixtureWithGivenUuid_NeverLogsErrorAndReturnsSameUuid(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithFixture(new FixtureResult('a')),
            new ThreadContext(),
        );
        $logger
            ->expects(self::never())
            ->method('error');
        self::assertSame('a', $lifecycle->updateFixture(fn () => null, 'a'));
    }

    public function testUpdateFixture_StorageProvidesStartedFixture_NeverLogsErrorAndReturnsMatchingUuid(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $threadContext = new ThreadContext();
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithFixture(new FixtureResult('a')),
            $threadContext,
        );
        $threadContext->push('a');
        $logger
            ->expects(self::never())
            ->method('error');
        self::assertSame('a', $lifecycle->updateFixture(fn () => null));
    }

    public function testUpdateFixture_NoExceptionThrownDuringUpdate_NotifiesHooksWithoutError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $fixture = new FixtureResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStorageWithFixture($fixture),
            new ThreadContext(),
        );

        $hooksNotifier
            ->expects(self::once())
            ->id('before')
            ->method('beforeFixtureUpdate')
            ->with(self::identicalTo($fixture));
        $hooksNotifier
            ->expects(self::never())
            ->method('onLifecycleError');
        $hooksNotifier
            ->expects(self::once())
            ->after('before')
            ->method('afterFixtureUpdate')
            ->with(self::identicalTo($fixture));
        $lifecycle->updateFixture(fn () => null, 'a');
    }

    public function testUpdateFixture_ExceptionThrownDuringUpdate_NotifiesHooksWithError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $fixture = new FixtureResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStorageWithFixture($fixture),
            new ThreadContext(),
        );

        $error = new Exception();
        $hooksNotifier
            ->expects(self::once())
            ->id('before')
            ->method('beforeFixtureUpdate')
            ->with(self::identicalTo($fixture));
        $hooksNotifier
            ->expects(self::once())
            ->id('error')
            ->after('before')
            ->method('onLifecycleError')
            ->with(self::identicalTo($error));
        $hooksNotifier
            ->expects(self::once())
            ->after('error')
            ->method('afterFixtureUpdate')
            ->with(self::identicalTo($fixture));
        $lifecycle->updateFixture(fn () => throw $error, 'a');
    }

    public function testUpdateFixture_StorageProvidesFixture_SameFixturePassedToCallback(): void
    {
        $fixture = new FixtureResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithFixture($fixture),
            new ThreadContext(),
        );

        $lifecycle->updateFixture(fn (FixtureResult $f) => $f->setName('b'), 'a');
        self::assertSame('b', $fixture->getName());
    }

    public function testStopFixture_FixtureNeitherGivenNorStarted_LogsErrorAndReturnsNull(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStub(ResultStorageInterface::class),
            new ThreadContext(),
        );

        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Fixture (UUID: {uuid}) not stopped'),
                self::equalTo(['uuid' => null, 'exception' => new ActiveTestNotFoundException()]),
            );
        self::assertNull($lifecycle->stopFixture());
    }

    public function testStopFixture_StorageFailsToProvideGivenFixture_LogsErrorAndReturnsNull(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $error = new Exception();
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithoutFixture('a', $error),
            new ThreadContext(),
        );

        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Fixture (UUID: {uuid}) not stopped'),
                self::identicalTo(['uuid' => 'a', 'exception' => $error]),
            );
        self::assertNull($lifecycle->stopFixture('a'));
    }

    public function testStopFixture_StorageFailsToProvideStartedFixture_LogsErrorAndReturnsNull(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $error = new Exception();
        $threadContext = new ThreadContext();
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithoutFixture('a', $error),
            $threadContext,
        );

        $threadContext->push('a');
        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Fixture (UUID: {uuid}) not stopped'),
                self::identicalTo(['uuid' => 'a', 'exception' => $error]),
            );
        self::assertNull($lifecycle->stopFixture());
    }

    public function testStopFixture_StorageProvidesFixtureWithGivenUuid_NeverLogsErrorAndReturnsSameUuid(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithFixture(new FixtureResult('a')),
            new ThreadContext(),
        );
        $logger
            ->expects(self::never())
            ->method('error');
        self::assertSame('a', $lifecycle->stopFixture('a'));
    }

    public function testStopFixture_StorageProvidesStartedFixture_NeverLogsErrorAndReturnsMatchingUuid(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $threadContext = new ThreadContext();
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithFixture(new FixtureResult('a')),
            $threadContext,
        );
        $threadContext->push('a');
        $logger
            ->expects(self::never())
            ->method('error');
        self::assertSame('a', $lifecycle->stopFixture());
    }

    public function testStopFixture_NoExceptionThrownDuringStop_NotifiesHooksWithoutError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $fixture = new FixtureResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStorageWithFixture($fixture),
            new ThreadContext(),
        );

        $hooksNotifier
            ->expects(self::once())
            ->id('before')
            ->method('beforeFixtureStop')
            ->with(self::identicalTo($fixture));
        $hooksNotifier
            ->expects(self::never())
            ->method('onLifecycleError');
        $hooksNotifier
            ->expects(self::once())
            ->after('before')
            ->method('afterFixtureStop')
            ->with(self::identicalTo($fixture));
        $lifecycle->stopFixture('a');
    }

    public function testStopFixture_ExceptionThrownDuringStop_NotifiesHooksWithError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $error = new Exception();
        $fixture = new FixtureResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createFailingClock($error),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStorageWithFixture($fixture),
            new ThreadContext(),
        );

        $hooksNotifier
            ->expects(self::once())
            ->id('before')
            ->method('beforeFixtureStop')
            ->with(self::identicalTo($fixture));
        $hooksNotifier
            ->expects(self::once())
            ->id('error')
            ->after('before')
            ->method('onLifecycleError')
            ->with(self::identicalTo($error));
        $hooksNotifier
            ->expects(self::once())
            ->after('error')
            ->method('afterFixtureStop')
            ->with(self::identicalTo($fixture));
        $lifecycle->stopFixture('a');
    }

    public function testStopFixture_ClockProvidesTime_FixtureStopIsSameTime(): void
    {
        $time = new DateTimeImmutable('@0');
        $fixture = new FixtureResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock($time),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithFixture($fixture),
            new ThreadContext(),
        );

        $lifecycle->stopFixture('a');
        self::assertSame($time, $fixture->getStop());
    }

    public function testStopFixture_StorageProvidesFixture_FixtureOnFinishedStage(): void
    {
        $time = new DateTimeImmutable('@0');
        $fixture = new FixtureResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock($time),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithFixture($fixture),
            new ThreadContext(),
        );

        $lifecycle->stopFixture('a');
        self::assertSame(Stage::finished(), $fixture->getStage());
    }

    public function testStopFixture_ThreadContextWithNonEmptyStack_ContextStackIsEmpty(): void
    {
        $threadContext = new ThreadContext();
        $fixture = new FixtureResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithFixture($fixture),
            $threadContext,
        );

        $threadContext->push('b');
        $threadContext->push('c');
        $lifecycle->stopFixture('a');
        self::assertSame([], $this->extractThreadStack($threadContext));
    }

    public function testStopFixture_StorageProvidesFixtureWithUuid_StorageUnsetsSameUuid(): void
    {
        $fixture = new FixtureResult('a');
        $resultStorage = $this->createMock(ResultStorageInterface::class);
        $resultStorage
            ->method('getFixture')
            ->with(self::identicalTo('a'))
            ->willReturn($fixture);
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $resultStorage,
            new ThreadContext(),
        );

        $resultStorage
            ->expects(self::once())
            ->method('unset')
            ->with(self::identicalTo('a'));
        $lifecycle->stopFixture('a');
    }

    public function testScheduleTest_NoExceptionThrownDuringSchedule_NotifiesHooksWithoutError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStub(ResultStorageInterface::class),
            new ThreadContext(),
        );

        $test = new TestResult('a');
        $hooksNotifier
            ->expects(self::once())
            ->id('before')
            ->method('beforeTestSchedule')
            ->with(self::identicalTo($test));
        $hooksNotifier
            ->expects(self::never())
            ->method('onLifecycleError');
        $hooksNotifier
            ->expects(self::once())
            ->after('before')
            ->method('afterTestSchedule')
            ->with(self::identicalTo($test));
        $lifecycle->scheduleTest($test);
    }

    public function testScheduleTest_ExceptionThrownDuringSchedule_NotifiesHooksWithError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $test = new TestResult('a');
        $error = new Exception();
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createNonSettableStorage($test, $error),
            new ThreadContext(),
        );

        $hooksNotifier
            ->expects(self::once())
            ->id('before')
            ->method('beforeTestSchedule')
            ->with(self::identicalTo($test));
        $hooksNotifier
            ->expects(self::once())
            ->id('error')
            ->after('before')
            ->method('onLifecycleError')
            ->with(self::identicalTo($error));
        $hooksNotifier
            ->expects(self::once())
            ->after('error')
            ->method('afterTestSchedule')
            ->with(self::identicalTo($test));
        $lifecycle->scheduleTest($test);
    }

    public function testScheduleTest_ExceptionThrownDuringScheduleWithoutContainer_LogsError(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $test = new TestResult('a');
        $error = new Exception();
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createNonSettableStorage($test, $error),
            new ThreadContext(),
        );

        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Test (UUID: {uuid}) not scheduled (container UUID: {containerUuid})'),
                self::identicalTo(['uuid' => 'a', 'containerUuid' => null, 'exception' => $error]),
            );
        $lifecycle->scheduleTest($test);
    }

    public function testScheduleTest_ExceptionThrownDuringScheduleWithContainer_LogsError(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $test = new TestResult('a');
        $error = new Exception();
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithContainer(new ContainerResult('b'), setError: $error),
            new ThreadContext(),
        );

        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Test (UUID: {uuid}) not scheduled (container UUID: {containerUuid})'),
                self::identicalTo(['uuid' => 'a', 'containerUuid' => 'b', 'exception' => $error]),
            );
        $lifecycle->scheduleTest($test, 'b');
    }

    public function testScheduleTest_GivenContainerUuid_TestAddedToContainerChildren(): void
    {
        $container = new ContainerResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithContainer($container),
            new ThreadContext(),
        );
        $test = new TestResult('b');
        $lifecycle->scheduleTest($test, 'a');
        self::assertSame([$test], $container->getChildren());
    }

    public function testScheduleTest_CurrentContainerUuid_TestAddedToContainerChildren(): void
    {
        $container = new ContainerResult('a');
        $threadContext = new ThreadContext();
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithContainer($container),
            $threadContext,
        );
        $threadContext->setContainer('a');
        $test = new TestResult('b');
        $lifecycle->scheduleTest($test);
        self::assertSame([$test], $container->getChildren());
    }

    public function testScheduleTest_GivenTest_SetsSameTestInStorage(): void
    {
        $resultStorage = $this->createMock(ResultStorageInterface::class);
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $resultStorage,
            new ThreadContext(),
        );

        $test = new TestResult('a');
        $resultStorage
            ->expects(self::once())
            ->method('set')
            ->with(self::identicalTo($test));
        $lifecycle->scheduleTest($test);
    }

    public function testScheduleTest_GivenTest_TestIsOnScheduledStage(): void
    {
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStub(ResultStorageInterface::class),
            new ThreadContext(),
        );
        $test = new TestResult('a');
        $lifecycle->scheduleTest($test);
        self::assertSame(Stage::scheduled(), $test->getStage());
    }

    public function testStartTest_StorageFailsToProvideTest_LogsError(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $error = new Exception();
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithoutTest('a', $error),
            new ThreadContext(),
        );

        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Test (UUID: {uuid}) not started'),
                self::identicalTo(['uuid' => 'a', 'exception' => $error]),
            );
        $lifecycle->startTest('a');
    }

    public function testStartTest_NoExceptionThrownDuringStart_NotifiesHooksWithoutError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $test = new TestResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStorageWithTest($test),
            new ThreadContext(),
        );

        $hooksNotifier
            ->expects(self::once())
            ->id('before')
            ->method('beforeTestStart')
            ->with(self::identicalTo($test));
        $hooksNotifier
            ->expects(self::never())
            ->method('onLifecycleError');
        $hooksNotifier
            ->expects(self::once())
            ->after('before')
            ->method('afterTestStart')
            ->with(self::identicalTo($test));
        $lifecycle->startTest('a');
    }

    public function testStartTest_ExceptionThrownDuringStart_NotifiesHooksWithError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $error = new Exception();
        $test = new TestResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createFailingClock($error),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStorageWithTest($test),
            new ThreadContext(),
        );

        $hooksNotifier
            ->expects(self::once())
            ->id('before')
            ->method('beforeTestStart')
            ->with(self::identicalTo($test));
        $hooksNotifier
            ->expects(self::once())
            ->id('error')
            ->after('before')
            ->method('onLifecycleError')
            ->with(self::identicalTo($error));
        $hooksNotifier
            ->expects(self::once())
            ->after('error')
            ->method('afterTestStart')
            ->with(self::identicalTo($test));
        $lifecycle->startTest('a');
    }

    public function testStartTest_ExceptionThrownDuringStart_LogsError(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $error = new Exception();
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createFailingClock($error),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithTest(new TestResult('a')),
            new ThreadContext(),
        );

        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Test (UUID: {uuid}) not started'),
                self::identicalTo(['uuid' => 'a', 'exception' => $error]),
            );
        $lifecycle->startTest('a');
    }

    public function testStartTest_ClockProvidesTime_TestStartIsSameTime(): void
    {
        $time = new DateTimeImmutable('@0');
        $test = new TestResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock($time),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithTest($test),
            new ThreadContext(),
        );

        $lifecycle->startTest('a');
        self::assertSame($time, $test->getStart());
    }

    public function testStartTest_StorageProvidesTest_TestIsOnRunningStage(): void
    {
        $test = new TestResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithTest($test),
            new ThreadContext(),
        );

        $lifecycle->startTest('a');
        self::assertSame(Stage::running(), $test->getStage());
    }

    public function testStartTest_ThreadContextWithNonEmptyStack_OnlyTestUuidInStack(): void
    {
        $test = new TestResult('a');
        $threadContext = new ThreadContext();
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithTest($test),
            $threadContext,
        );

        $threadContext->push('b');
        $threadContext->push('c');
        $lifecycle->startTest('a');
        self::assertSame(['a'], $this->extractThreadStack($threadContext));
    }

    public function testUpdateTest_TestNeitherGivenNorStarted_LogsErrorAndReturnsNull(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStub(ResultStorageInterface::class),
            new ThreadContext(),
        );

        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Test (UUID: {uuid}) not updated'),
                self::equalTo(['uuid' => null, 'exception' => new ActiveTestNotFoundException()])
            );
        self::assertNull($lifecycle->updateTest(fn () => null));
    }

    public function testUpdateTest_StorageFailsToProvideTest_LogsErrorAndReturnsNull(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $error = new Exception();
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithoutTest('a', $error),
            new ThreadContext(),
        );

        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Test (UUID: {uuid}) not updated'),
                self::identicalTo(['uuid' => 'a', 'exception' => $error])
            );
        self::assertNull($lifecycle->updateTest(fn () => null, 'a'));
    }

    public function testUpdateTest_StorageProvidesGivenTest_LogsNoErrorAndReturnsTestUuid(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithTest(new TestResult('a')),
            new ThreadContext(),
        );

        $logger
            ->expects(self::never())
            ->method('error');
        self::assertSame('a', $lifecycle->updateTest(fn () => null, 'a'));
    }

    public function testUpdateTest_StorageProvidesStartedTest_LogsNoErrorAndReturnsTestUuid(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $threadContext = new ThreadContext();
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithTest(new TestResult('a')),
            $threadContext,
        );

        $threadContext->push('a');
        $logger
            ->expects(self::never())
            ->method('error');
        self::assertSame('a', $lifecycle->updateTest(fn () => null));
    }

    public function testUpdateTest_NoExceptionThrownDuringUpdate_NotifiesHooksWithoutError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $test = new TestResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStorageWithTest($test),
            new ThreadContext(),
        );

        $hooksNotifier
            ->expects(self::once())
            ->id('before')
            ->method('beforeTestUpdate')
            ->with(self::identicalTo($test));
        $hooksNotifier
            ->expects(self::never())
            ->method('onLifecycleError');
        $hooksNotifier
            ->expects(self::once())
            ->after('before')
            ->method('afterTestUpdate')
            ->with(self::identicalTo($test));
        $lifecycle->updateTest(fn () => null, 'a');
    }

    public function testUpdateTest_ExceptionThrownDuringUpdate_NotifiesHooksWithError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $test = new TestResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStorageWithTest($test),
            new ThreadContext(),
        );

        $error = new Exception();
        $hooksNotifier
            ->expects(self::once())
            ->id('before')
            ->method('beforeTestUpdate')
            ->with(self::identicalTo($test));
        $hooksNotifier
            ->expects(self::once())
            ->id('error')
            ->after('before')
            ->method('onLifecycleError')
            ->with(self::identicalTo($error));
        $hooksNotifier
            ->expects(self::once())
            ->after('error')
            ->method('afterTestUpdate')
            ->with(self::identicalTo($test));
        $lifecycle->updateTest(fn () => throw $error, 'a');
    }

    public function testUpdateTest_ExceptionThrownDuringUpdate_LogsErrorAndReturnsTestUuid(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $test = new TestResult('a');
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithTest($test),
            new ThreadContext(),
        );

        $error = new Exception();
        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Test (UUID: {uuid}) not updated'),
                self::identicalTo(['uuid' => 'a', 'exception' => $error]),
            );
        self::assertSame('a', $lifecycle->updateTest(fn () => throw $error, 'a'));
    }

    public function testUpdateTest_StorageProvidesTest_SameTestPassedToCallback(): void
    {
        $test = new TestResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithTest($test),
            new ThreadContext(),
        );

        $lifecycle->updateTest(fn (TestResult $t) => $t->setName('b'), 'a');
        self::assertSame('b', $test->getName());
    }

    public function testStopTest_TestNeitherGivenNorStarted_LogsErrorAndReturnsNull(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStub(ResultStorageInterface::class),
            new ThreadContext(),
        );

        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Test (UUID: {uuid}) not stopped'),
                self::equalTo(['uuid' => null, 'exception' => new ActiveTestNotFoundException()]),
            );
        self::assertNull($lifecycle->stopTest());
    }

    public function testStopTest_StorageFailsToProvideGivenTest_LogsErrorAndReturnsNull(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $error = new Exception();
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithoutTest('a', $error),
            new ThreadContext(),
        );

        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Test (UUID: {uuid}) not stopped'),
                self::identicalTo(['uuid' => 'a', 'exception' => $error]),
            );
        self::assertNull($lifecycle->stopTest('a'));
    }

    public function testStopTest_StorageFailsToProvideStartedTest_LogsErrorAndReturnsNull(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $error = new Exception();
        $threadContext = new ThreadContext();
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithoutTest('a', $error),
            $threadContext,
        );

        $threadContext->push('a');
        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Test (UUID: {uuid}) not stopped'),
                self::identicalTo(['uuid' => 'a', 'exception' => $error]),
            );
        self::assertNull($lifecycle->stopTest());
    }

    public function testStopTest_StorageProvidesStartedTest_NeverLogsErrorAndReturnsTestUuid(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithTest(new TestResult('a')),
            new ThreadContext(),
        );

        $logger
            ->expects(self::never())
            ->method('error');
        self::assertSame('a', $lifecycle->stopTest('a'));
    }

    public function testStopTest_NoExceptionThrownDuringStop_NotifiesHooksWithoutError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $test = new TestResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStorageWithTest($test),
            new ThreadContext(),
        );

        $hooksNotifier
            ->expects(self::once())
            ->id('before')
            ->method('beforeTestStop')
            ->with(self::identicalTo($test));
        $hooksNotifier
            ->expects(self::never())
            ->method('onLifecycleError');
        $hooksNotifier
            ->expects(self::once())
            ->after('before')
            ->method('afterTestStop')
            ->with(self::identicalTo($test));
        $lifecycle->stopTest('a');
    }

    public function testStopTest_ExceptionThrownDuringStop_NotifiesHooksWithError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $error = new Exception();
        $test = new TestResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createFailingClock($error),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStorageWithTest($test),
            new ThreadContext(),
        );

        $hooksNotifier
            ->expects(self::once())
            ->id('before')
            ->method('beforeTestStop')
            ->with(self::identicalTo($test));
        $hooksNotifier
            ->expects(self::once())
            ->id('error')
            ->after('before')
            ->method('onLifecycleError')
            ->with(self::identicalTo($error));
        $hooksNotifier
            ->expects(self::once())
            ->after('error')
            ->method('afterTestStop')
            ->with(self::identicalTo($test));
        $lifecycle->stopTest('a');
    }

    public function testStopTest_ExceptionThrownDuringStop_LogsErrorAndReturnsTestUuid(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $error = new Exception();
        $test = new TestResult('a');
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createFailingClock($error),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithTest($test),
            new ThreadContext(),
        );

        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Test (UUID: {uuid}) not stopped'),
                self::identicalTo(['uuid' => 'a', 'exception' => $error]),
            );
        self::assertSame('a', $lifecycle->stopTest('a'));
    }

    public function testStopTest_ClockProvidesTime_TestStopIsSameTime(): void
    {
        $time = new DateTimeImmutable('@0');
        $test = new TestResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock($time),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithTest($test),
            new ThreadContext(),
        );

        $lifecycle->stopTest('a');
        self::assertSame($time, $test->getStop());
    }

    public function testStopTest_StorageProvidesTest_TestOnFinishedStage(): void
    {
        $test = new TestResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithTest($test),
            new ThreadContext(),
        );

        $lifecycle->stopTest('a');
        self::assertSame(Stage::finished(), $test->getStage());
    }

    public function testStopTest_ThreadContextWithNonEmptyStack_ContextHasEmptyStack(): void
    {
        $test = new TestResult('a');
        $threadContext = new ThreadContext();
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithTest($test),
            $threadContext,
        );

        $threadContext->push('b');
        $threadContext->push('c');
        $lifecycle->stopTest('a');
        self::assertSame([], $this->extractThreadStack($threadContext));
    }

    public function testWriteTest_StorageFailsToProvideTest_LogsError(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $error = new Exception();
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithoutTest('a', $error),
            new ThreadContext(),
        );

        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Test (UUID: {uuid}) not written'),
                self::identicalTo(['uuid' => 'a', 'exception' => $error]),
            );
        $lifecycle->writeTest('a');
    }

    public function testWriteTest_NoExceptionThrownDuringWrite_NotifiesHooksWithoutError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $test = new TestResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStorageWithTest($test),
            new ThreadContext(),
        );

        $hooksNotifier
            ->expects(self::once())
            ->id('before')
            ->method('beforeTestWrite')
            ->with(self::identicalTo($test));
        $hooksNotifier
            ->expects(self::never())
            ->method('onLifecycleError');
        $hooksNotifier
            ->expects(self::once())
            ->after('before')
            ->method('afterTestWrite')
            ->with(self::identicalTo($test));
        $lifecycle->writeTest('a');
    }

    public function testWriteTest_ExceptionThrownDuringWrite_NotifiesHooksWithError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $test = new TestResult('a');
        $error = new Exception();
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStorageWithTest($test, unsetError: $error),
            new ThreadContext(),
        );

        $hooksNotifier
            ->expects(self::once())
            ->id('before')
            ->method('beforeTestWrite')
            ->with(self::identicalTo($test));
        $hooksNotifier
            ->expects(self::once())
            ->id('error')
            ->after('before')
            ->method('onLifecycleError')
            ->with(self::identicalTo($error));
        $hooksNotifier
            ->expects(self::once())
            ->after('error')
            ->method('afterTestWrite')
            ->with(self::identicalTo($test));
        $lifecycle->writeTest('a');
    }

    public function testWriteTest_ExceptionThrownDuringWrite_LogsError(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $test = new TestResult('a');
        $error = new Exception();
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithTest($test, unsetError: $error),
            new ThreadContext(),
        );

        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Test (UUID: {uuid}) not written'),
                self::identicalTo(['uuid' => 'a', 'exception' => $error]),
            );
        $lifecycle->writeTest('a');
    }

    public function testWriteTest_StorageProvidesTest_StorageUnsetsSameTest(): void
    {
        $test = new TestResult('a');
        $resultStorage = $this->createMock(ResultStorageInterface::class);
        $resultStorage
            ->method('getTest')
            ->with(self::identicalTo('a'))
            ->willReturn($test);
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $resultStorage,
            new ThreadContext(),
        );

        $resultStorage
            ->expects(self::once())
            ->method('unset')
            ->with(self::identicalTo('a'));
        $lifecycle->writeTest('a');
    }

    public function testWriteTest_StorageProvidesNonExcludedTest_WriterWritesSameTest(): void
    {
        $test = new TestResult('a');
        $resultsWriter = $this->createMock(ResultsWriterInterface::class);
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $resultsWriter,
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithTest($test),
            new ThreadContext(),
        );

        $resultsWriter
            ->expects(self::once())
            ->method('writeTest')
            ->with(self::identicalTo($test));
        $lifecycle->writeTest('a');
    }

    public function testWriteTest_StorageProvidesExcludedTest_WriterNeverWritesTest(): void
    {
        $resultWriter = $this->createMock(ResultsWriterInterface::class);
        $test = new TestResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $resultWriter,
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithTest($test),
            new ThreadContext(),
        );

        $test->setExcluded(true);
        $resultWriter
            ->expects(self::never())
            ->method('writeTest');
        $lifecycle->writeTest('a');
    }

    public function testWriteTest_ExcludedTestWithNestedResults_RemovesNestedResults(): void
    {
        $resultsWriter = $this->createMock(ResultsWriterInterface::class);

        $test = new TestResult('a');
        $testAttachment = new AttachmentResult('b');
        $test->addAttachments($testAttachment);
        $testStep = new StepResult('c');
        $testStepAttachment = new AttachmentResult('d');
        $testStep->addAttachments($testStepAttachment);
        $test->addSteps($testStep);

        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $resultsWriter,
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithTest($test),
            new ThreadContext(),
        );

        $test->setExcluded(true);
        $resultsWriter
            ->expects(self::exactly(2))
            ->method('removeAttachment')
            ->withConsecutive(
                [self::identicalTo($testAttachment)],
                [self::identicalTo($testStepAttachment)],
            );
        $lifecycle->writeTest('a');
    }

    public function testWriteTest_WriterFailsToRemoveAttachment_LogsError(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $error = new Exception();
        $resultsWriter = $this->createStub(ResultsWriterInterface::class);
        $resultsWriter
            ->method('removeAttachment')
            ->willThrowException($error);

        $test = new TestResult('a');
        $testAttachment = new AttachmentResult('b');
        $test->addAttachments($testAttachment->setExcluded(true));

        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $resultsWriter,
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithTest($test),
            new ThreadContext(),
        );

        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Attachment (UUID: {uuid}) not removed'),
                self::identicalTo(['uuid' => 'b', 'exception' => $error]),
            );
        $lifecycle->writeTest('a');
    }

    public function testStartStep_ParentNeitherGivenNorStarted_LogsError(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStub(ResultStorageInterface::class),
            new ThreadContext(),
        );

        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Step (UUID: {uuid}) not started (parent UUID: {parentUuid})'),
                self::equalTo(
                    [
                        'uuid' => 'a',
                        'parentUuid' => null,
                        'exception' => new ActiveExecutionContextNotFoundException(),
                    ]
                ),
            );
        $lifecycle->startStep(new StepResult('a'));
    }

    public function testStartStep_StorageFailsToProvideGivenExecutionContext_LogsError(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $error = new Exception();
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithoutExecutionContext('a', $error),
            new ThreadContext(),
        );

        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Step (UUID: {uuid}) not started (parent UUID: {parentUuid})'),
                self::equalTo(['uuid' => 'b', 'parentUuid' => 'a', 'exception' => $error]),
            );
        $lifecycle->startStep(new StepResult('b'), 'a');
    }

    public function testStartStep_StorageFailsToProvideStartedTest_LogsError(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $error = new Exception();
        $threadContext = new ThreadContext();
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithoutExecutionContext('a', $error),
            $threadContext,
        );

        $threadContext->push('a');
        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Step (UUID: {uuid}) not started (parent UUID: {parentUuid})'),
                self::equalTo(['uuid' => 'b', 'parentUuid' => 'a', 'exception' => $error]),
            );
        $lifecycle->startStep(new StepResult('b'));
    }

    public function testStartStep_StorageFailsToProvideStartedStep_LogsError(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $error = new Exception();
        $threadContext = new ThreadContext();
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithoutExecutionContext('b', $error),
            $threadContext,
        );

        $threadContext->push('a');
        $threadContext->push('b');
        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Step (UUID: {uuid}) not started (parent UUID: {parentUuid})'),
                self::equalTo(['uuid' => 'c', 'parentUuid' => 'b', 'exception' => $error]),
            );
        $lifecycle->startStep(new StepResult('c'));
    }

    public function testStartStep_StorageProvidesGivenExecutionContext_NeverLogsError(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithTest(new TestResult('a')),
            new ThreadContext(),
        );

        $logger
            ->expects(self::never())
            ->method('error');
        $lifecycle->startStep(new StepResult('b'), 'a');
    }

    public function testStartStep_StorageProvidesStartedTest_NeverLogsError(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $threadContext = new ThreadContext();
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithTest(new TestResult('a')),
            $threadContext,
        );

        $threadContext->push('a');
        $logger
            ->expects(self::never())
            ->method('error');
        $lifecycle->startStep(new StepResult('b'));
    }

    public function testStartStep_StorageProvidesStartedStep_NeverLogsError(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $threadContext = new ThreadContext();
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithStep(new StepResult('b')),
            $threadContext,
        );

        $threadContext->push('a');
        $threadContext->push('b');
        $logger
            ->expects(self::never())
            ->method('error');
        $lifecycle->startStep(new StepResult('c'));
    }

    public function testStartStep_NoExceptionThrownDuringStart_NotifiesHooksWithoutError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStorageWithTest(new TestResult('a')),
            new ThreadContext(),
        );

        $step = new StepResult('b');
        $hooksNotifier
            ->expects(self::once())
            ->id('before')
            ->method('beforeStepStart')
            ->with(self::identicalTo($step));
        $hooksNotifier
            ->expects(self::never())
            ->method('onLifecycleError');
        $hooksNotifier
            ->expects(self::once())
            ->after('before')
            ->method('afterStepStart')
            ->with(self::identicalTo($step));
        $lifecycle->startStep($step, 'a');
    }

    public function testStartStep_ExceptionThrownDuringStart_NotifiesHooksWithError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $error = new Exception();
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createFailingClock($error),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStorageWithTest(new TestResult('a')),
            new ThreadContext(),
        );

        $step = new StepResult('b');
        $hooksNotifier
            ->expects(self::once())
            ->id('before')
            ->method('beforeStepStart')
            ->with(self::identicalTo($step));
        $hooksNotifier
            ->expects(self::once())
            ->id('error')
            ->after('before')
            ->method('onLifecycleError')
            ->with(self::identicalTo($error));
        $hooksNotifier
            ->expects(self::once())
            ->after('error')
            ->method('afterStepStart')
            ->with(self::identicalTo($step));
        $lifecycle->startStep($step, 'a');
    }

    public function testStartStep_StorageProvidesParent_StepIsAmongParentChildren(): void
    {
        $test = new TestResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithTest($test),
            new ThreadContext(),
        );

        $step = new StepResult('b');
        $lifecycle->startStep($step, 'a');
        self::assertSame([$step], $test->getSteps());
    }

    public function testStartStep_StepWithGivenUuid_StorageSetsSameUuid(): void
    {
        $resultStorage = $this->createMock(ResultStorageInterface::class);
        $resultStorage
            ->method('getExecutionContext')
            ->willReturn(new TestResult('a'));
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $resultStorage,
            new ThreadContext(),
        );

        $step = new StepResult('b');
        $resultStorage
            ->expects(self::once())
            ->method('set')
            ->with(self::identicalTo($step));
        $lifecycle->startStep($step, 'a');
    }

    public function testStartStep_ClockProvidesTime_StepStartIsSameTime(): void
    {
        $time = new DateTimeImmutable('@0');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock($time),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithTest(new TestResult('a')),
            new ThreadContext(),
        );

        $step = new StepResult('b');
        $lifecycle->startStep($step, 'a');
        self::assertSame($time, $step->getStart());
    }

    public function testStartStep_GivenStep_StepOnRunningStage(): void
    {
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithTest(new TestResult('a')),
            new ThreadContext(),
        );

        $step = new StepResult('b');
        $lifecycle->startStep($step, 'a');
        self::assertSame(Stage::running(), $step->getStage());
    }

    public function testStartStep_ThreadContextWithNonEmptyStack_StepUuidAddedToStack(): void
    {
        $threadContext = new ThreadContext();
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithTest(new TestResult('a')),
            $threadContext,
        );

        $threadContext->push('b');
        $threadContext->push('c');
        $step = new StepResult('d');
        $lifecycle->startStep($step, 'a');
        self::assertSame(['d', 'c', 'b'], $this->extractThreadStack($threadContext));
    }

    public function testUpdateStep_StepNeitherGivenNorStarted_LogsErrorAndReturnsNull(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStub(ResultStorageInterface::class),
            new ThreadContext(),
        );

        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Step (UUID: {uuid}) not updated'),
                self::equalTo(['uuid' => null, 'exception' => new ActiveStepNotFoundException()]),
            );
        self::assertNull($lifecycle->updateStep(fn () => null));
    }

    public function testUpdateStep_StorageFailsToProvideGivenStep_LogsErrorAndReturnsNull(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $error = new Exception();
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithoutStep('a', $error),
            new ThreadContext(),
        );

        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Step (UUID: {uuid}) not updated'),
                self::equalTo(['uuid' => 'a', 'exception' => $error]),
            );
        self::assertNull($lifecycle->updateStep(fn () => null, 'a'));
    }

    public function testUpdateStep_StorageFailsToProvideStartedStep_LogsErrorAndReturnsNull(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $error = new Exception();
        $threadContext = new ThreadContext();
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithoutStep('b', $error),
            $threadContext,
        );

        $threadContext->push('a');
        $threadContext->push('b');
        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Step (UUID: {uuid}) not updated'),
                self::equalTo(['uuid' => 'b', 'exception' => $error]),
            );
        self::assertNull($lifecycle->updateStep(fn () => null));
    }

    public function testUpdateStep_NoExceptionThrownDuringUpdate_NotifiesHooksWithoutError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $step = new StepResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStorageWithStep($step),
            new ThreadContext(),
        );

        $hooksNotifier
            ->expects(self::once())
            ->id('before')
            ->method('beforeStepUpdate')
            ->with(self::identicalTo($step));
        $hooksNotifier
            ->expects(self::never())
            ->method('onLifecycleError');
        $hooksNotifier
            ->expects(self::once())
            ->after('before')
            ->method('afterStepUpdate')
            ->with(self::identicalTo($step));
        $lifecycle->updateStep(fn () => null, 'a');
    }

    public function testUpdateStep_ExceptionThrownDuringUpdate_NotifiesHooksWithError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $step = new StepResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStorageWithStep($step),
            new ThreadContext(),
        );

        $error = new Exception();
        $hooksNotifier
            ->expects(self::once())
            ->id('before')
            ->method('beforeStepUpdate')
            ->with(self::identicalTo($step));
        $hooksNotifier
            ->expects(self::once())
            ->id('error')
            ->after('before')
            ->method('onLifecycleError')
            ->with(self::identicalTo($error));
        $hooksNotifier
            ->expects(self::once())
            ->after('error')
            ->method('afterStepUpdate')
            ->with(self::identicalTo($step));
        $lifecycle->updateStep(fn () => throw $error, 'a');
    }

    public function testUpdateStep_ExceptionNotThrownDuringUpdate_NeverLogsErrorAndReturnsUuid(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithStep(new StepResult('a')),
            new ThreadContext(),
        );

        $logger
            ->expects(self::never())
            ->method('error');
        self::assertSame('a', $lifecycle->updateStep(fn () => null, 'a'));
    }

    public function testUpdateStep_ExceptionThrownDuringUpdate_LogsErrorAndReturnsUuid(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithStep(new StepResult('a')),
            new ThreadContext(),
        );

        $error = new Exception();
        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Step (UUID: {uuid}) not updated'),
                self::equalTo(['uuid' => 'a', 'exception' => $error]),
            );
        self::assertSame('a', $lifecycle->updateStep(fn () => throw $error, 'a'));
    }

    public function testUpdateStep_StorageProvidesStep_SameStepPassedToCallback(): void
    {
        $step = new StepResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithStep($step),
            new ThreadContext(),
        );
        $lifecycle->updateStep(fn (StepResult $s) => $s->setName('b'), 'a');
        self::assertSame('b', $step->getName());
    }

    public function testUpdateExecutionContext_ContextNeitherGivenNorStarted_LogsErrorAndReturnsNull(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStub(ResultStorageInterface::class),
            new ThreadContext(),
        );

        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Execution context (UUID: {uuid}) not updated'),
                self::equalTo(['uuid' => null, 'exception' => new ActiveExecutionContextNotFoundException()]),
            );
        $lifecycle->updateExecutionContext(fn () => null);
    }

    public function testUpdateExecutionContext_StorageFailsToProvideGivenContext_LogsErrorAndReturnsNull(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $error = new Exception();
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithoutExecutionContext('a', $error),
            new ThreadContext(),
        );

        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Execution context (UUID: {uuid}) not updated'),
                self::equalTo(['uuid' => 'a', 'exception' => $error]),
            );
        self::assertNull($lifecycle->updateExecutionContext(fn () => null, 'a'));
    }

    public function testUpdateExecutionContext_StorageFailsToProvideStartedContext_LogsErrorAndReturnsNull(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $error = new Exception();
        $threadContext = new ThreadContext();
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithoutExecutionContext('b', $error),
            $threadContext,
        );

        $threadContext->push('a');
        $threadContext->push('b');
        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Execution context (UUID: {uuid}) not updated'),
                self::equalTo(['uuid' => 'b', 'exception' => $error]),
            );
        self::assertNull($lifecycle->updateExecutionContext(fn () => null));
    }

    public function testUpdateExecutionContext_StorageProvidesInvalidContext_LogsErrorAndReturnsNull(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithInvalidExecutionContext('a'),
            new ThreadContext(),
        );

        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Execution context (UUID: {uuid}) not updated'),
                self::equalTo(['uuid' => 'a']),
            );
        self::assertNull($lifecycle->updateExecutionContext(fn () => null, 'a'));
    }

    public function testUpdateExecutionContext_StorageProvidesGivenTest_NeverLogsErrorAndReturnsUuid(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithTest(new TestResult('a')),
            new ThreadContext(),
        );

        $logger
            ->expects(self::never())
            ->method('error');
        self::assertSame('a', $lifecycle->updateExecutionContext(fn () => null, 'a'));
    }

    public function testUpdateExecutionContext_StorageProvidesGivenFixture_NeverLogsErrorAndReturnsUuid(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithFixture(new FixtureResult('a')),
            new ThreadContext(),
        );

        $logger
            ->expects(self::never())
            ->method('error');
        self::assertSame('a', $lifecycle->updateExecutionContext(fn () => null, 'a'));
    }

    public function testUpdateExecutionContext_StorageProvidesGivenStep_NeverLogsErrorAndReturnsUuid(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithStep(new StepResult('a')),
            new ThreadContext(),
        );

        $logger
            ->expects(self::never())
            ->method('error');
        self::assertSame('a', $lifecycle->updateExecutionContext(fn () => null, 'a'));
    }

    public function testUpdateExecutionContext_StorageProvidesStartedStep_NeverLogsErrorAndReturnsUuid(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $threadContext = new ThreadContext();
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithStep(new StepResult('b')),
            $threadContext,
        );

        $threadContext->push('a');
        $threadContext->push('b');
        $logger
            ->expects(self::never())
            ->method('error');
        self::assertSame('b', $lifecycle->updateExecutionContext(fn () => null));
    }

    public function testUpdateExecutionContext_ContextNeitherGivenNorStarted_NotifiesHooksWithError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStub(ResultStorageInterface::class),
            new ThreadContext(),
        );

        $hooksNotifier
            ->expects(self::once())
            ->method('onLifecycleError')
            ->with(self::isInstanceOf(ActiveExecutionContextNotFoundException::class));
        $lifecycle->updateExecutionContext(fn () => null);
    }

    public function testUpdateExecutionContext_StorageFailsToProvideContext_NotifiesHooksWithError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $error = new Exception();
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStorageWithoutExecutionContext('a', $error),
            new ThreadContext(),
        );

        $hooksNotifier
            ->expects(self::once())
            ->method('onLifecycleError')
            ->with(self::identicalTo($error));
        $lifecycle->updateExecutionContext(fn () => null, 'a');
    }

    public function testUpdateExecutionContext_StorageProvidesInvalidContext_NotifiesHooksWithError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStorageWithInvalidExecutionContext('a'),
            new ThreadContext(),
        );

        $hooksNotifier
            ->expects(self::once())
            ->method('onLifecycleError')
            ->with(self::isInstanceOf(InvalidExecutionContextException::class));
        $lifecycle->updateExecutionContext(fn () => null, 'a');
    }

    public function testUpdateExecutionContext_StorageProvidesTest_UpdatesSameTest(): void
    {
        $test = new TestResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithTest($test),
            new ThreadContext(),
        );

        $lifecycle->updateExecutionContext(fn (ExecutionContextInterface $c) => $c->setName('b'), 'a');
        self::assertSame('b', $test->getName());
    }

    public function testUpdateExecutionContext_StorageProvidesFixture_UpdatesSameFixture(): void
    {
        $fixture = new FixtureResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithFixture($fixture),
            new ThreadContext(),
        );

        $lifecycle->updateExecutionContext(fn (ExecutionContextInterface $c) => $c->setName('b'), 'a');
        self::assertSame('b', $fixture->getName());
    }

    public function testUpdateExecutionContext_StorageProvidesStep_UpdatesSameStep(): void
    {
        $step = new StepResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithStep($step),
            new ThreadContext(),
        );

        $lifecycle->updateExecutionContext(fn (ExecutionContextInterface $c) => $c->setName('b'), 'a');
        self::assertSame('b', $step->getName());
    }

    public function testUpdateExecutionContext_NoExceptionThrownDuringTestUpdate_NotifiesHooksWithoutError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $test = new TestResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStorageWithTest($test),
            new ThreadContext(),
        );

        $hooksNotifier
            ->expects(self::once())
            ->id('before')
            ->method('beforeTestUpdate')
            ->with(self::identicalTo($test));
        $hooksNotifier
            ->expects(self::never())
            ->method('onLifecycleError');
        $hooksNotifier
            ->expects(self::once())
            ->after('before')
            ->method('afterTestUpdate')
            ->with(self::identicalTo($test));
        $lifecycle->updateExecutionContext(fn () => null, 'a');
    }

    public function testUpdateExecutionContext_ExceptionThrownDuringTestUpdate_NotifiesHooksWithError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $test = new TestResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStorageWithTest($test),
            new ThreadContext(),
        );

        $error = new Exception();
        $hooksNotifier
            ->expects(self::once())
            ->id('before')
            ->method('beforeTestUpdate')
            ->with(self::identicalTo($test));
        $hooksNotifier
            ->expects(self::once())
            ->id('error')
            ->after('before')
            ->method('onLifecycleError')
            ->with(self::identicalTo($error));
        $hooksNotifier
            ->expects(self::once())
            ->after('error')
            ->method('afterTestUpdate')
            ->with(self::identicalTo($test));
        $lifecycle->updateExecutionContext(fn () => throw $error, 'a');
    }

    public function testUpdateExecutionContext_NoExceptionThrownDuringFixtureUpdate_NotifiesHooksWithoutError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $fixture = new FixtureResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStorageWithFixture($fixture),
            new ThreadContext(),
        );

        $hooksNotifier
            ->expects(self::once())
            ->id('before')
            ->method('beforeFixtureUpdate')
            ->with(self::identicalTo($fixture));
        $hooksNotifier
            ->expects(self::never())
            ->method('onLifecycleError');
        $hooksNotifier
            ->expects(self::once())
            ->after('before')
            ->method('afterFixtureUpdate')
            ->with(self::identicalTo($fixture));
        $lifecycle->updateExecutionContext(fn () => null, 'a');
    }

    public function testUpdateExecutionContext_ExceptionThrownDuringFixtureUpdate_NotifiesHooksWithError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $fixture = new FixtureResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStorageWithFixture($fixture),
            new ThreadContext(),
        );

        $error = new Exception();
        $hooksNotifier
            ->expects(self::once())
            ->id('before')
            ->method('beforeFixtureUpdate')
            ->with(self::identicalTo($fixture));
        $hooksNotifier
            ->expects(self::once())
            ->id('error')
            ->after('before')
            ->method('onLifecycleError')
            ->with(self::identicalTo($error));
        $hooksNotifier
            ->expects(self::once())
            ->after('error')
            ->method('afterFixtureUpdate')
            ->with(self::identicalTo($fixture));
        $lifecycle->updateExecutionContext(fn () => throw $error, 'a');
    }

    public function testUpdateExecutionContext_NoExceptionThrownDuringStepUpdate_NotifiesHooksWithoutError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $step = new StepResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStorageWithStep($step),
            new ThreadContext(),
        );

        $hooksNotifier
            ->expects(self::once())
            ->id('before')
            ->method('beforeStepUpdate')
            ->with(self::identicalTo($step));
        $hooksNotifier
            ->expects(self::never())
            ->method('onLifecycleError');
        $hooksNotifier
            ->expects(self::once())
            ->after('before')
            ->method('afterStepUpdate')
            ->with(self::identicalTo($step));
        $lifecycle->updateExecutionContext(fn () => null, 'a');
    }

    public function testUpdateExecutionContext_ExceptionThrownDuringStepUpdate_NotifiesHooksWithError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $step = new StepResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStorageWithStep($step),
            new ThreadContext(),
        );

        $error = new Exception();
        $hooksNotifier
            ->expects(self::once())
            ->id('before')
            ->method('beforeStepUpdate')
            ->with(self::identicalTo($step));
        $hooksNotifier
            ->expects(self::once())
            ->id('error')
            ->after('before')
            ->method('onLifecycleError')
            ->with(self::identicalTo($error));
        $hooksNotifier
            ->expects(self::once())
            ->after('error')
            ->method('afterStepUpdate')
            ->with(self::identicalTo($step));
        $lifecycle->updateExecutionContext(fn () => throw $error, 'a');
    }

    public function testUpdateExecutionContext_ExceptionThrownDuringTestUpdate_LogsErrorAndReturnsUuid(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $test = new TestResult('a');
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithTest($test),
            new ThreadContext(),
        );

        $error = new Exception();
        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Test (UUID: {uuid}) not updated'),
                self::identicalTo(['uuid' => 'a', 'exception' => $error]),
            );
        self::assertSame('a', $lifecycle->updateExecutionContext(fn () => throw $error, 'a'));
    }

    public function testUpdateExecutionContext_ExceptionThrownDuringFixtureUpdate_LogsErrorAndReturnsUuid(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $fixture = new FixtureResult('a');
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithFixture($fixture),
            new ThreadContext(),
        );

        $error = new Exception();
        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Fixture (UUID: {uuid}) not updated'),
                self::identicalTo(['uuid' => 'a', 'exception' => $error]),
            );
        self::assertSame('a', $lifecycle->updateExecutionContext(fn () => throw $error, 'a'));
    }

    public function testUpdateExecutionContext_ExceptionThrownDuringStepUpdate_LogsErrorAndReturnsUuid(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $step = new StepResult('a');
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithStep($step),
            new ThreadContext(),
        );

        $error = new Exception();
        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Step (UUID: {uuid}) not updated'),
                self::identicalTo(['uuid' => 'a', 'exception' => $error]),
            );
        self::assertSame('a', $lifecycle->updateExecutionContext(fn () => throw $error, 'a'));
    }

    public function testStopStep_StepNeitherGivenNorStarted_LogsErrorAndReturnsNull(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStub(ResultStorageInterface::class),
            new ThreadContext(),
        );

        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Step (UUID: {uuid}) not stopped'),
                self::equalTo(['uuid' => null, 'exception' => new ActiveStepNotFoundException()]),
            );
        self::assertNull($lifecycle->stopStep());
    }

    public function testStopStep_StorageFailsToProvideGivenStep_LogsErrorAndReturnsNull(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $error = new Exception();
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithoutStep('a', $error),
            new ThreadContext(),
        );

        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Step (UUID: {uuid}) not stopped'),
                self::identicalTo(['uuid' => 'a', 'exception' => $error]),
            );
        self::assertNull($lifecycle->stopStep('a'));
    }

    public function testStopStep_StorageFailsToProvideStartedStep_LogsErrorAndReturnsNull(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $error = new Exception();
        $threadContext = new ThreadContext();
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithoutStep('b', $error),
            $threadContext,
        );

        $threadContext->push('a');
        $threadContext->push('b');
        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Step (UUID: {uuid}) not stopped'),
                self::identicalTo(['uuid' => 'b', 'exception' => $error]),
            );
        self::assertNull($lifecycle->stopStep());
    }

    public function testStopStep_StepNeitherGivenNorStarted_NotifiesHooksWithError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStub(ResultStorageInterface::class),
            new ThreadContext(),
        );

        $hooksNotifier
            ->expects(self::once())
            ->method('onLifecycleError')
            ->with(self::isInstanceOf(ActiveStepNotFoundException::class));
        $lifecycle->stopStep();
    }

    public function testStopStep_StorageFailsToProvideGivenStep_NotifiesHooksWithError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $error = new Exception();
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStorageWithoutStep('a', $error),
            new ThreadContext(),
        );

        $hooksNotifier
            ->expects(self::once())
            ->method('onLifecycleError')
            ->with(self::identicalTo($error));
        $lifecycle->stopStep('a');
    }

    public function testStopStep_StorageFailsToProvideStartedStep_NotifiesHooksWithError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $error = new Exception();
        $threadContext = new ThreadContext();
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStorageWithoutStep('b', $error),
            $threadContext,
        );

        $threadContext->push('a');
        $threadContext->push('b');
        $hooksNotifier
            ->expects(self::once())
            ->method('onLifecycleError')
            ->with(self::identicalTo($error));
        $lifecycle->stopStep();
    }

    public function testStopStep_StorageProvidesGivenStep_NeverLogsErrorAndReturnsUuid(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithStep(new StepResult('a')),
            new ThreadContext(),
        );

        $logger
            ->expects(self::never())
            ->method('error');
        self::assertSame('a', $lifecycle->stopStep('a'));
    }

    public function testStopStep_StorageProvidesStartedStep_NeverLogsErrorAndReturnsUuid(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $threadContext = new ThreadContext();
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithStep(new StepResult('b')),
            $threadContext,
        );

        $threadContext->push('a');
        $threadContext->push('b');
        $logger
            ->expects(self::never())
            ->method('error');
        self::assertSame('b', $lifecycle->stopStep());
    }

    public function testStopStep_NoExceptionThrownDuringStop_NotifiesHooksWithoutError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $step = new StepResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStorageWithStep($step),
            new ThreadContext(),
        );

        $hooksNotifier
            ->expects(self::once())
            ->id('before')
            ->method('beforeStepStop')
            ->with(self::identicalTo($step));
        $hooksNotifier
            ->expects(self::never())
            ->method('onLifecycleError');
        $hooksNotifier
            ->expects(self::once())
            ->after('before')
            ->method('afterStepStop')
            ->with(self::identicalTo($step));
        $lifecycle->stopStep('a');
    }

    public function testStopStep_ExceptionThrownDuringStop_NotifiesHooksWithError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $error = new Exception();
        $step = new StepResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createFailingClock($error),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStorageWithStep($step),
            new ThreadContext(),
        );

        $hooksNotifier
            ->expects(self::once())
            ->id('before')
            ->method('beforeStepStop')
            ->with(self::identicalTo($step));
        $hooksNotifier
            ->expects(self::once())
            ->id('error')
            ->after('before')
            ->method('onLifecycleError');
        $hooksNotifier
            ->expects(self::once())
            ->after('error')
            ->method('afterStepStop')
            ->with(self::identicalTo($step));
        $lifecycle->stopStep('a');
    }

    public function testStopStep_ExceptionThrownDuringStop_LogsErrorAndReturnsUuid(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $error = new Exception();
        $step = new StepResult('a');
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createFailingClock($error),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithStep($step),
            new ThreadContext(),
        );

        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Step (UUID: {uuid}) not stopped'),
                self::identicalTo(['uuid' => 'a', 'exception' => $error]),
            );
        self::assertSame('a', $lifecycle->stopStep('a'));
    }

    public function testStopStep_ClockProvidesTime_StepStopIsSameTime(): void
    {
        $time = new DateTimeImmutable('@0');
        $step = new StepResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock($time),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithStep($step),
            new ThreadContext(),
        );

        $lifecycle->stopStep('a');
        self::assertSame($time, $step->getStop());
    }

    public function testStopStep_StorageProvidesStep_StepOnFinishedStage(): void
    {
        $time = new DateTimeImmutable('@0');
        $step = new StepResult('a');
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock($time),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithStep($step),
            new ThreadContext(),
        );

        $lifecycle->stopStep('a');
        self::assertSame($time, $step->getStop());
    }

    public function testStopStep_StorageProvidesStep_StorageUnsetsSameStep(): void
    {
        $resultStorage = $this->createMock(ResultStorageInterface::class);
        $resultStorage
            ->method('getStep')
            ->with(self::identicalTo('a'))
            ->willReturn(new StepResult('a'));
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $resultStorage,
            new ThreadContext(),
        );

        $resultStorage
            ->expects(self::once())
            ->method('unset')
            ->with(self::identicalTo('a'));
        $lifecycle->stopStep('a');
    }

    public function testStopStep_ThreadContextWithNonEmptyStack_TopValueIsRemovedFromStack(): void
    {
        $threadContext = new ThreadContext();
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithStep(new StepResult('a')),
            $threadContext,
        );

        $threadContext->push('b');
        $threadContext->push('c');
        $lifecycle->stopStep('a');
        self::assertSame(['b'], $this->extractThreadStack($threadContext));
    }

    public function testAddAttachment_ThreadContextWithEmptyStack_LogsError(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStub(ResultStorageInterface::class),
            new ThreadContext(),
        );

        $attachment = new AttachmentResult('a');
        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Attachment (UUID: {uuid}) not added (parent UUID: {parentUuid})'),
                self::equalTo(
                    [
                        'uuid' => 'a',
                        'parentUuid' => null,
                        'exception' => new ActiveExecutionContextNotFoundException(),
                    ],
                ),
            );
        $lifecycle->addAttachment(
            $attachment,
            $this->createStub(DataSourceInterface::class),
        );
    }

    public function testAddAttachment_StorageFailedToProvideStartedTest_LogsError(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $error = new Exception();
        $threadContext = new ThreadContext();
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithoutTest('a', $error),
            $threadContext,
        );

        $threadContext->push('a');
        $attachment = new AttachmentResult('b');
        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Attachment (UUID: {uuid}) not added (parent UUID: {parentUuid})'),
                self::identicalTo(
                    [
                        'uuid' => 'b',
                        'parentUuid' => 'a',
                        'exception' => $error,
                    ],
                ),
            );
        $lifecycle->addAttachment(
            $attachment,
            $this->createStub(DataSourceInterface::class),
        );
    }

    public function testAddAttachment_StorageFailedToProvideStartedStep_LogsError(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $error = new Exception();
        $threadContext = new ThreadContext();
        $lifecycle = new AllureLifecycle(
            $logger,
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithoutStep('b', $error),
            $threadContext,
        );

        $threadContext->push('a');
        $threadContext->push('b');
        $attachment = new AttachmentResult('c');
        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Attachment (UUID: {uuid}) not added (parent UUID: {parentUuid})'),
                self::identicalTo(
                    [
                        'uuid' => 'c',
                        'parentUuid' => 'b',
                        'exception' => $error,
                    ],
                ),
            );
        $lifecycle->addAttachment(
            $attachment,
            $this->createStub(DataSourceInterface::class),
        );
    }

    public function testAddAttachment_ThreadContextWithEmptyStack_NotifiesHooksWithError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStub(ResultStorageInterface::class),
            new ThreadContext(),
        );

        $attachment = new AttachmentResult('a');
        $hooksNotifier
            ->expects(self::once())
            ->method('onLifecycleError')
            ->with(self::isInstanceOf(ActiveExecutionContextNotFoundException::class));
        $lifecycle->addAttachment(
            $attachment,
            $this->createStub(DataSourceInterface::class),
        );
    }

    public function testAddAttachment_StorageFailsToProvideStartedTest_NotifiesHooksWithError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $error = new Exception();
        $threadContext = new ThreadContext();
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStorageWithoutTest('a', $error),
            $threadContext,
        );

        $threadContext->push('a');
        $attachment = new AttachmentResult('b');
        $hooksNotifier
            ->expects(self::once())
            ->method('onLifecycleError')
            ->with(self::identicalTo($error));
        $lifecycle->addAttachment(
            $attachment,
            $this->createStub(DataSourceInterface::class),
        );
    }

    public function testAddAttachment_StorageFailsToProvideStartedStep_NotifiesHooksWithError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $error = new Exception();
        $threadContext = new ThreadContext();
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStorageWithoutStep('b', $error),
            $threadContext,
        );

        $threadContext->push('a');
        $threadContext->push('b');
        $attachment = new AttachmentResult('c');
        $hooksNotifier
            ->expects(self::once())
            ->method('onLifecycleError')
            ->with(self::identicalTo($error));
        $lifecycle->addAttachment(
            $attachment,
            $this->createStub(DataSourceInterface::class),
        );
    }

    public function testAddAttachment_NoExceptionThrownDuringWrite_NotifiesHooksWithoutError(): void
    {
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $threadContext = new ThreadContext();
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $this->createStub(ResultsWriterInterface::class),
            $hooksNotifier,
            $this->createStorageWithTest(new TestResult('a')),
            $threadContext,
        );

        $threadContext->push('a');
        $attachment = new AttachmentResult('b');
        $hooksNotifier
            ->expects(self::once())
            ->id('before')
            ->method('beforeAttachmentWrite')
            ->with(self::identicalTo($attachment));
        $hooksNotifier
            ->expects(self::never())
            ->method('onLifecycleError');
        $hooksNotifier
            ->expects(self::once())
            ->after('before')
            ->method('afterAttachmentWrite')
            ->with(self::identicalTo($attachment));
        $lifecycle->addAttachment(
            $attachment,
            $this->createStub(DataSourceInterface::class),
        );
    }

    public function testAddAttachment_ExceptionThrownDuringWrite_NotifiesHooksWithError(): void
    {
        $error = new Exception();
        $resultsWriter = $this->createStub(ResultsWriterInterface::class);
        $resultsWriter
            ->method('writeAttachment')
            ->willThrowException($error);
        $hooksNotifier = $this->createMock(HooksNotifierInterface::class);
        $threadContext = new ThreadContext();
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $resultsWriter,
            $hooksNotifier,
            $this->createStorageWithTest(new TestResult('a')),
            $threadContext,
        );

        $threadContext->push('a');
        $attachment = new AttachmentResult('b');
        $hooksNotifier
            ->expects(self::once())
            ->id('before')
            ->method('beforeAttachmentWrite')
            ->with(self::identicalTo($attachment));
        $hooksNotifier
            ->expects(self::once())
            ->id('error')
            ->after('before')
            ->method('onLifecycleError')
            ->with(self::identicalTo($error));
        $hooksNotifier
            ->expects(self::once())
            ->after('error')
            ->method('afterAttachmentWrite')
            ->with(self::identicalTo($attachment));
        $lifecycle->addAttachment(
            $attachment,
            $this->createStub(DataSourceInterface::class),
        );
    }

    public function testAddAttachment_AttachmentNotExcluded_WriterWritesSameAttachmentWithGivenData(): void
    {
        $resultsWriter = $this->createMock(ResultsWriterInterface::class);
        $threadContext = new ThreadContext();
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $resultsWriter,
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithTest(new TestResult('a')),
            $threadContext,
        );

        $threadContext->push('a');
        $attachment = new AttachmentResult('b');
        $data = $this->createStub(DataSourceInterface::class);
        $resultsWriter
            ->expects(self::once())
            ->method('writeAttachment')
            ->with(self::identicalTo($attachment), self::identicalTo($data));
        $lifecycle->addAttachment($attachment, $data);
    }

    public function testAddAttachment_AttachmentExcluded_WriterNeverWritesAttachment(): void
    {
        $resultsWriter = $this->createMock(ResultsWriterInterface::class);
        $threadContext = new ThreadContext();
        $lifecycle = new AllureLifecycle(
            $this->createStub(LoggerInterface::class),
            $this->createClock(),
            $resultsWriter,
            $this->createStub(HooksNotifierInterface::class),
            $this->createStorageWithTest(new TestResult('a')),
            $threadContext,
        );

        $threadContext->push('a');
        $attachment = new AttachmentResult('b');
        $resultsWriter
            ->expects(self::never())
            ->method('writeAttachment');
        $lifecycle->addAttachment(
            $attachment->setExcluded(true),
            $this->createStub(DataSourceInterface::class),
        );
    }

    private function extractThreadStack(ThreadContextInterface $threadContext): array
    {
        $items = [];
        while (null !== $item = $threadContext->getCurrentTestOrStep()) {
            $items[] = $item;
            $threadContext->pop();
        }

        return $items;
    }

    private function createStorageWithContainer(
        ContainerResult $container,
        ?Throwable $setError = null,
        ?Throwable $unsetError = null,
    ): ResultStorageInterface {
        $resultStorage = $this->createMock(ResultStorageInterface::class);
        $resultStorage
            ->method('getContainer')
            ->with(self::identicalTo($container->getUuid()))
            ->willReturn($container);
        if (isset($setError)) {
            $resultStorage
                ->method('set')
                ->willThrowException($setError);
        }
        if (isset($unsetError)) {
            $resultStorage
                ->method('unset')
                ->willThrowException($unsetError);
        }

        return $resultStorage;
    }

    private function createStorageWithoutContainer(string $uuid, Throwable $error): ResultStorageInterface
    {
        $resultStorage = $this->createMock(ResultStorageInterface::class);
        $resultStorage
            ->method('getContainer')
            ->with(self::identicalTo($uuid))
            ->willThrowException($error);

        return $resultStorage;
    }

    private function createStorageWithFixture(FixtureResult $fixture): ResultStorageInterface
    {
        $resultStorage = $this->createMock(ResultStorageInterface::class);
        $resultStorage
            ->method('getFixture')
            ->with(self::identicalTo($fixture->getUuid()))
            ->willReturn($fixture);
        $resultStorage
            ->method('getExecutionContext')
            ->with(self::identicalTo($fixture->getUuid()))
            ->willReturn($fixture);

        return $resultStorage;
    }

    private function createStorageWithoutFixture(string $uuid, Throwable $error): ResultStorageInterface
    {
        $resultStorage = $this->createMock(ResultStorageInterface::class);
        $resultStorage
            ->method('getFixture')
            ->with(self::identicalTo($uuid))
            ->willThrowException($error);
        $resultStorage
            ->method('getExecutionContext')
            ->with(self::identicalTo($uuid))
            ->willThrowException($error);

        return $resultStorage;
    }

    private function createStorageWithTest(TestResult $test, ?Throwable $unsetError = null): ResultStorageInterface
    {
        $resultStorage = $this->createMock(ResultStorageInterface::class);
        $resultStorage
            ->method('getTest')
            ->with(self::identicalTo($test->getUuid()))
            ->willReturn($test);
        $resultStorage
            ->method('getExecutionContext')
            ->with(self::identicalTo($test->getUuid()))
            ->willReturn($test);
        if (isset($unsetError)) {
            $resultStorage
                ->method('unset')
                ->willThrowException($unsetError);
        }

        return $resultStorage;
    }

    private function createStorageWithoutTest(string $uuid, Throwable $error): ResultStorageInterface
    {
        $resultStorage = $this->createMock(ResultStorageInterface::class);
        $resultStorage
            ->method('getTest')
            ->with(self::identicalTo($uuid))
            ->willThrowException($error);
        $resultStorage
            ->method('getExecutionContext')
            ->with(self::identicalTo($uuid))
            ->willThrowException($error);

        return $resultStorage;
    }

    private function createStorageWithStep(StepResult $step): ResultStorageInterface
    {
        $resultStorage = $this->createMock(ResultStorageInterface::class);
        $resultStorage
            ->method('getStep')
            ->with(self::identicalTo($step->getUuid()))
            ->willReturn($step);
        $resultStorage
            ->method('getExecutionContext')
            ->with(self::identicalTo($step->getUuid()))
            ->willReturn($step);

        return $resultStorage;
    }

    private function createStorageWithoutStep(string $uuid, Throwable $error): ResultStorageInterface
    {
        $resultStorage = $this->createMock(ResultStorageInterface::class);
        $resultStorage
            ->method('getStep')
            ->with(self::identicalTo($uuid))
            ->willThrowException($error);
        $resultStorage
            ->method('getExecutionContext')
            ->with(self::identicalTo($uuid))
            ->willThrowException($error);

        return $resultStorage;
    }

    private function createStorageWithInvalidExecutionContext(string $uuid): ResultStorageInterface
    {
        $context = $this->createStub(ExecutionContextInterface::class);
        $context
            ->method('getUuid')
            ->willReturn($uuid);
        $resultStorage = $this->createMock(ResultStorageInterface::class);
        $resultStorage
            ->method('getExecutionContext')
            ->with(self::identicalTo($uuid))
            ->willReturn($context);

        return $resultStorage;
    }

    private function createStorageWithoutExecutionContext(string $uuid, Throwable $error): ResultStorageInterface
    {
        $resultStorage = $this->createMock(ResultStorageInterface::class);
        $resultStorage
            ->method('getExecutionContext')
            ->with(self::identicalTo($uuid))
            ->willThrowException($error);

        return $resultStorage;
    }

    private function createNonSettableStorage(StorableResultInterface $result, Throwable $error): ResultStorageInterface
    {
        $resultStorage = $this->createMock(ResultStorageInterface::class);
        $resultStorage
            ->method('set')
            ->with(self::identicalTo($result))
            ->willThrowException($error);

        return $resultStorage;
    }

    private function createClock(?DateTimeImmutable $time = null): ClockInterface
    {
        $clock = $this->createStub(ClockInterface::class);
        $clock
            ->method('now')
            ->willReturn($time ?? new DateTimeImmutable('@0'));

        return $clock;
    }

    private function createFailingClock(Throwable $error): ClockInterface
    {
        $clock = $this->createStub(ClockInterface::class);
        $clock
            ->method('now')
            ->willThrowException($error);

        return $clock;
    }
}
