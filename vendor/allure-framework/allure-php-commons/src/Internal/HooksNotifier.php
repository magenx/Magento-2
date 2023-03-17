<?php

declare(strict_types=1);

namespace Qameta\Allure\Internal;

use Psr\Log\LoggerInterface;
use Qameta\Allure\Hook\AfterAttachmentWriteHookInterface;
use Qameta\Allure\Hook\AfterContainerStartHookInterface;
use Qameta\Allure\Hook\AfterContainerStopHookInterface;
use Qameta\Allure\Hook\AfterContainerUpdateHookInterface;
use Qameta\Allure\Hook\AfterContainerWriteHookInterface;
use Qameta\Allure\Hook\AfterFixtureStartHookInterface;
use Qameta\Allure\Hook\AfterFixtureStopHookInterface;
use Qameta\Allure\Hook\AfterFixtureUpdateHookInterface;
use Qameta\Allure\Hook\AfterStepStartHookInterface;
use Qameta\Allure\Hook\AfterStepStopHookInterface;
use Qameta\Allure\Hook\AfterStepUpdateHookInterface;
use Qameta\Allure\Hook\AfterTestScheduleHookInterface;
use Qameta\Allure\Hook\AfterTestStartHookInterface;
use Qameta\Allure\Hook\AfterTestStopHookInterface;
use Qameta\Allure\Hook\AfterTestUpdateHookInterface;
use Qameta\Allure\Hook\AfterTestWriteHookInterface;
use Qameta\Allure\Hook\BeforeAttachmentWriteHookInterface;
use Qameta\Allure\Hook\BeforeContainerStartHookInterface;
use Qameta\Allure\Hook\BeforeContainerStopHookInterface;
use Qameta\Allure\Hook\BeforeContainerUpdateHookInterface;
use Qameta\Allure\Hook\BeforeContainerWriteHookInterface;
use Qameta\Allure\Hook\BeforeFixtureStartHookInterface;
use Qameta\Allure\Hook\BeforeFixtureStopHookInterface;
use Qameta\Allure\Hook\BeforeFixtureUpdateHookInterface;
use Qameta\Allure\Hook\BeforeStepStartHookInterface;
use Qameta\Allure\Hook\BeforeStepStopHookInterface;
use Qameta\Allure\Hook\BeforeStepUpdateHookInterface;
use Qameta\Allure\Hook\BeforeTestScheduleHookInterface;
use Qameta\Allure\Hook\BeforeTestStartHookInterface;
use Qameta\Allure\Hook\BeforeTestStopHookInterface;
use Qameta\Allure\Hook\BeforeTestUpdateHookInterface;
use Qameta\Allure\Hook\BeforeTestWriteHookInterface;
use Qameta\Allure\Hook\LifecycleHookInterface;
use Qameta\Allure\Hook\OnLifecycleErrorHookInterface;
use Qameta\Allure\Model\AttachmentResult;
use Qameta\Allure\Model\ContainerResult;
use Qameta\Allure\Model\FixtureResult;
use Qameta\Allure\Model\ResultType;
use Qameta\Allure\Model\StepResult;
use Qameta\Allure\Model\TestResult;
use Throwable;

use function array_values;

final class HooksNotifier implements HooksNotifierInterface
{
    use LoggerAwareTrait;

    /**
     * @var list<LifecycleHookInterface>
     */
    private array $hooks;

    public function __construct(
        LoggerInterface $logger,
        LifecycleHookInterface ...$hooks,
    ) {
        $this->logger = $logger;
        $this->hooks = array_values($hooks);
    }

    public function beforeContainerStart(ContainerResult $container): void
    {
        $this->forEachHook(
            $container->getResultType(),
            BeforeContainerStartHookInterface::class,
            static fn (BeforeContainerStartHookInterface $hook) => $hook->beforeContainerStart($container),
        );
    }

    public function afterContainerStart(ContainerResult $container): void
    {
        $this->forEachHook(
            $container->getResultType(),
            AfterContainerStartHookInterface::class,
            static fn (AfterContainerStartHookInterface $hook) => $hook->afterContainerStart($container),
        );
    }

    public function beforeContainerUpdate(ContainerResult $container): void
    {
        $this->forEachHook(
            $container->getResultType(),
            BeforeContainerUpdateHookInterface::class,
            static fn (BeforeContainerUpdateHookInterface $hook) => $hook->beforeContainerUpdate($container),
        );
    }

    public function afterContainerUpdate(ContainerResult $container): void
    {
        $this->forEachHook(
            $container->getResultType(),
            AfterContainerUpdateHookInterface::class,
            static fn (AfterContainerUpdateHookInterface $hook) => $hook->afterContainerUpdate($container),
        );
    }

    public function beforeContainerStop(ContainerResult $container): void
    {
        $this->forEachHook(
            $container->getResultType(),
            BeforeContainerStopHookInterface::class,
            static fn (BeforeContainerStopHookInterface $hook) => $hook->beforeContainerStop($container),
        );
    }

    public function afterContainerStop(ContainerResult $container): void
    {
        $this->forEachHook(
            $container->getResultType(),
            AfterContainerStopHookInterface::class,
            static fn (AfterContainerStopHookInterface $hook) => $hook->afterContainerStop($container),
        );
    }

    public function beforeContainerWrite(ContainerResult $container): void
    {
        $this->forEachHook(
            $container->getResultType(),
            BeforeContainerWriteHookInterface::class,
            static fn (BeforeContainerWriteHookInterface $hook) => $hook->beforeContainerWrite($container),
        );
    }

    public function afterContainerWrite(ContainerResult $container): void
    {
        $this->forEachHook(
            $container->getResultType(),
            AfterContainerWriteHookInterface::class,
            static fn (AfterContainerWriteHookInterface $hook) => $hook->afterContainerWrite($container),
        );
    }

    public function beforeFixtureStart(FixtureResult $fixture): void
    {
        $this->forEachHook(
            $fixture->getResultType(),
            BeforeFixtureStartHookInterface::class,
            static fn (BeforeFixtureStartHookInterface $hook) => $hook->beforeFixtureStart($fixture),
        );
    }

    public function afterFixtureStart(FixtureResult $fixture): void
    {
        $this->forEachHook(
            $fixture->getResultType(),
            AfterFixtureStartHookInterface::class,
            static fn (AfterFixtureStartHookInterface $hook) => $hook->afterFixtureStart($fixture),
        );
    }

    public function beforeFixtureUpdate(FixtureResult $fixture): void
    {
        $this->forEachHook(
            $fixture->getResultType(),
            BeforeFixtureUpdateHookInterface::class,
            static fn (BeforeFixtureUpdateHookInterface $hook) => $hook->beforeFixtureUpdate($fixture),
        );
    }

    public function afterFixtureUpdate(FixtureResult $fixture): void
    {
        $this->forEachHook(
            $fixture->getResultType(),
            AfterFixtureUpdateHookInterface::class,
            static fn (AfterFixtureUpdateHookInterface $hook) => $hook->afterFixtureUpdate($fixture),
        );
    }

    public function beforeFixtureStop(FixtureResult $fixture): void
    {
        $this->forEachHook(
            $fixture->getResultType(),
            BeforeFixtureStopHookInterface::class,
            static fn (BeforeFixtureStopHookInterface $hook) => $hook->beforeFixtureStop($fixture),
        );
    }

    public function afterFixtureStop(FixtureResult $fixture): void
    {
        $this->forEachHook(
            $fixture->getResultType(),
            AfterFixtureStopHookInterface::class,
            static fn (AfterFixtureStopHookInterface $hook) => $hook->afterFixtureStop($fixture),
        );
    }

    public function beforeTestSchedule(TestResult $test): void
    {
        $this->forEachHook(
            $test->getResultType(),
            BeforeTestScheduleHookInterface::class,
            static fn (BeforeTestScheduleHookInterface $hook) => $hook->beforeTestSchedule($test),
        );
    }

    public function afterTestSchedule(TestResult $test): void
    {
        $this->forEachHook(
            $test->getResultType(),
            AfterTestScheduleHookInterface::class,
            static fn (AfterTestScheduleHookInterface $hook) => $hook->afterTestSchedule($test),
        );
    }

    public function beforeTestStart(TestResult $test): void
    {
        $this->forEachHook(
            $test->getResultType(),
            BeforeTestStartHookInterface::class,
            static fn (BeforeTestStartHookInterface $hook) => $hook->beforeTestStart($test),
        );
    }

    public function afterTestStart(TestResult $test): void
    {
        $this->forEachHook(
            $test->getResultType(),
            AfterTestStartHookInterface::class,
            static fn (AfterTestStartHookInterface $hook) => $hook->afterTestStart($test),
        );
    }

    public function beforeTestUpdate(TestResult $test): void
    {
        $this->forEachHook(
            $test->getResultType(),
            BeforeTestUpdateHookInterface::class,
            static fn (BeforeTestUpdateHookInterface $hook) => $hook->beforeTestUpdate($test),
        );
    }

    public function afterTestUpdate(TestResult $test): void
    {
        $this->forEachHook(
            $test->getResultType(),
            AfterTestUpdateHookInterface::class,
            static fn (AfterTestUpdateHookInterface $hook) => $hook->afterTestUpdate($test),
        );
    }

    public function beforeTestStop(TestResult $test): void
    {
        $this->forEachHook(
            $test->getResultType(),
            BeforeTestStopHookInterface::class,
            static fn (BeforeTestStopHookInterface $hook) => $hook->beforeTestStop($test),
        );
    }

    public function afterTestStop(TestResult $test): void
    {
        $this->forEachHook(
            $test->getResultType(),
            AfterTestStopHookInterface::class,
            static fn (AfterTestStopHookInterface $hook) => $hook->afterTestStop($test),
        );
    }

    public function beforeTestWrite(TestResult $test): void
    {
        $this->forEachHook(
            $test->getResultType(),
            BeforeTestWriteHookInterface::class,
            static fn (BeforeTestWriteHookInterface $hook) => $hook->beforeTestWrite($test),
        );
    }

    public function afterTestWrite(TestResult $test): void
    {
        $this->forEachHook(
            $test->getResultType(),
            AfterTestWriteHookInterface::class,
            static fn (AfterTestWriteHookInterface $hook) => $hook->afterTestWrite($test),
        );
    }

    public function beforeStepStart(StepResult $step): void
    {
        $this->forEachHook(
            $step->getResultType(),
            BeforeStepStartHookInterface::class,
            static fn (BeforeStepStartHookInterface $hook) => $hook->beforeStepStart($step),
        );
    }

    public function afterStepStart(StepResult $step): void
    {
        $this->forEachHook(
            $step->getResultType(),
            AfterStepStartHookInterface::class,
            static fn (AfterStepStartHookInterface $hook) => $hook->afterStepStart($step),
        );
    }

    public function beforeStepUpdate(StepResult $step): void
    {
        $this->forEachHook(
            $step->getResultType(),
            BeforeStepUpdateHookInterface::class,
            static fn (BeforeStepUpdateHookInterface $hook) => $hook->beforeStepUpdate($step),
        );
    }

    public function afterStepUpdate(StepResult $step): void
    {
        $this->forEachHook(
            $step->getResultType(),
            AfterStepUpdateHookInterface::class,
            static fn (AfterStepUpdateHookInterface $hook) => $hook->afterStepUpdate($step),
        );
    }

    public function beforeStepStop(StepResult $step): void
    {
        $this->forEachHook(
            $step->getResultType(),
            BeforeStepStopHookInterface::class,
            static fn (BeforeStepStopHookInterface $hook) => $hook->beforeStepStop($step),
        );
    }

    public function afterStepStop(StepResult $step): void
    {
        $this->forEachHook(
            $step->getResultType(),
            AfterStepStopHookInterface::class,
            static fn (AfterStepStopHookInterface $hook) => $hook->afterStepStop($step),
        );
    }

    public function beforeAttachmentWrite(AttachmentResult $attachment): void
    {
        $this->forEachHook(
            $attachment->getResultType(),
            BeforeAttachmentWriteHookInterface::class,
            static fn (BeforeAttachmentWriteHookInterface $hook) => $hook->beforeAttachmentWrite($attachment),
        );
    }

    public function afterAttachmentWrite(AttachmentResult $attachment): void
    {
        $this->forEachHook(
            $attachment->getResultType(),
            AfterAttachmentWriteHookInterface::class,
            static fn (AfterAttachmentWriteHookInterface $hook) => $hook->afterAttachmentWrite($attachment),
        );
    }

    public function onLifecycleError(Throwable $error): void
    {
        $this->forEachHook(
            ResultType::unknown(),
            OnLifecycleErrorHookInterface::class,
            static fn (OnLifecycleErrorHookInterface $hook) => $hook->onLifecycleError($error),
        );
    }

    /**
     * @template T of LifecycleHookInterface
     * @param ResultType       $resultType
     * @param class-string<T>  $hookClass
     * @param callable(T):void $callable
     */
    private function forEachHook(
        ResultType $resultType,
        string $hookClass,
        callable $callable,
    ): void {
        foreach ($this->hooks as $hook) {
            if ($hook instanceof $hookClass) {
                $this->runNotification($resultType, $callable, $hook);
            }
        }
    }

    /**
     * @template T of LifecycleHookInterface
     * @param ResultType       $resultType
     * @param callable(T):void $callable
     * @param T                $hook
     */
    private function runNotification(
        ResultType $resultType,
        callable $callable,
        LifecycleHookInterface $hook,
    ): void {
        try {
            $callable($hook);
        } catch (Throwable $e) {
            $this->logException("{$resultType} hook (class: {class}) failed", $e, ['class' => $hook::class]);
        }
    }
}
