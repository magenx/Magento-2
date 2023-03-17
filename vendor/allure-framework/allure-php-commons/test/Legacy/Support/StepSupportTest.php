<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Legacy\Support;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Allure;
use Qameta\Allure\AllureLifecycleInterface;
use Qameta\Allure\Model\ResultFactoryInterface;
use Qameta\Allure\Model\StepResult;
use Qameta\Allure\Setup\LifecycleBuilderInterface;
use Qameta\Allure\StepContextInterface;
use Yandex\Allure\Adapter\Support\StepSupport;

/**
 * @covers \Yandex\Allure\Adapter\Support\StepSupport
 */
class StepSupportTest extends TestCase
{
    public function setUp(): void
    {
        Allure::reset();
    }

    public function testStepSupport_GivenNameWithoutTitle_StepNameEqualsToGivenName(): void
    {
        $step = new StepResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithStep($step),
                $this->createLifecycleWithUpdatableStep($step),
            ),
        );

        /**
         * @psalm-suppress DeprecatedTrait
         */
        $object = new class () {
            use StepSupport;
        };

        $object->executeStep('b', fn (StepContextInterface $_) => null);
        self::assertSame('b', $step->getName());
    }

    public function testStepSupport_GivenNameWithTitle_StepNameEqualsToGivenTitle(): void
    {
        $step = new StepResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithStep($step),
                $this->createLifecycleWithUpdatableStep($step),
            ),
        );

        /**
         * @psalm-suppress DeprecatedTrait
         */
        $object = new class () {
            use StepSupport;
        };

        $object->executeStep('b', fn (StepContextInterface $_) => null, 'c');
        self::assertSame('c', $step->getName());
    }

    public function testStepSupport_CallbackSetsStepNameInContext_StepHasSameName(): void
    {
        $step = new StepResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithStep($step),
                $this->createLifecycleWithUpdatableStep($step),
            ),
        );

        /**
         * @psalm-suppress DeprecatedTrait
         */
        $object = new class () {
            use StepSupport;
        };

        $object->executeStep('b', fn (StepContextInterface $s) => $s->name('c'));
        self::assertSame('c', $step->getName());
    }

    /**
     * @dataProvider providerReturnValue
     */
    public function testStepSupport_CallbackReturnsValue_ReturnsSameValue(mixed $value): void
    {
        $step = new StepResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithStep($step),
                $this->createLifecycleWithUpdatableStep($step),
            ),
        );

        /**
         * @psalm-suppress DeprecatedTrait
         */
        $object = new class () {
            use StepSupport;
        };

        /** @var mixed $result */
        $result = $object->executeStep('b', fn (StepContextInterface $_): mixed => $value);
        self::assertSame($value, $result);
    }

    /**
     * @return iterable<string, array{mixed}>
     */
    public static function providerReturnValue(): iterable
    {
        return [
            'Null' => [null],
            'Scalar' => ['a'],
            'Array' => [[1, 2]],
            'Object' => [(object) ['a' => 'b']],
        ];
    }

    private function createLifecycleBuilder(
        ?ResultFactoryInterface $resultFactory = null,
        ?AllureLifecycleInterface $lifecycle = null,
    ): LifecycleBuilderInterface {
        $builder = $this->createStub(LifecycleBuilderInterface::class);
        if (isset($resultFactory)) {
            $builder
                ->method('getResultFactory')
                ->willReturn($resultFactory);
        }
        if (isset($lifecycle)) {
            $builder
                ->method('createLifecycle')
                ->willReturn($lifecycle);
        }

        return $builder;
    }

    private function createResultFactoryWithStep(StepResult $step): ResultFactoryInterface
    {
        $resultFactory = $this->createStub(ResultFactoryInterface::class);
        $resultFactory
            ->method('createStep')
            ->willReturn($step);

        return $resultFactory;
    }

    private function createLifecycleWithUpdatableStep(StepResult $step): AllureLifecycleInterface
    {
        $lifecycle = $this->createMock(AllureLifecycleInterface::class);
        $lifecycle
            ->method('updateStep')
            ->with(
                self::callback(
                    function (callable $callable) use ($step): bool {
                        $callable($step);

                        return true;
                    },
                ),
                self::identicalTo($step->getUuid()),
            );
        $lifecycle
            ->method('updateExecutionContext')
            ->with(
                self::callback(
                    function (callable $callable) use ($step): bool {
                        $callable($step);

                        return true;
                    },
                ),
            );

        return $lifecycle;
    }
}
