<?php

declare(strict_types=1);

namespace Qameta\Allure\Test;

use Exception;
use PHPUnit\Framework\TestCase;
use Qameta\Allure\Allure;
use Qameta\Allure\AllureLifecycleInterface;
use Qameta\Allure\Attribute\DisplayName;
use Qameta\Allure\Attribute\Parameter;
use Qameta\Allure\Internal\LifecycleBuilder;
use Qameta\Allure\Io\ResultsWriterInterface;
use Qameta\Allure\Io\StreamDataSource;
use Qameta\Allure\Io\StringDataSource;
use Qameta\Allure\Model\AttachmentResult;
use Qameta\Allure\Model\LinkType;
use Qameta\Allure\Model\ParameterMode;
use Qameta\Allure\Model\ResultFactoryInterface;
use Qameta\Allure\Model\Severity;
use Qameta\Allure\Model\Status;
use Qameta\Allure\Model\StatusDetails;
use Qameta\Allure\Model\StepResult;
use Qameta\Allure\Model\TestResult;
use Qameta\Allure\Setup\LifecycleBuilderInterface;
use Qameta\Allure\Setup\StatusDetectorInterface;
use Qameta\Allure\StepContextInterface;
use Throwable;

use const STDOUT;

/**
 * @covers \Qameta\Allure\Allure
 */
class AllureTest extends TestCase
{
    public function setUp(): void
    {
        Allure::reset();
    }

    public function testGetLifecycleConfigurator_BuilderSet_ReturnsSameBuilder(): void
    {
        $builder = $this->createLifecycleBuilder();
        Allure::setLifecycleBuilder($builder);
        self::assertSame($builder, Allure::getLifecycleConfigurator());
    }

    public function testGetLifecycleConfigurator_BuilderNotSet_ReturnsLifecycleBuilderInstance(): void
    {
        self::assertInstanceOf(LifecycleBuilder::class, Allure::getLifecycleConfigurator());
    }

    public function testGetConfig_ConstructedWithBuilder_ReturnsSameInstance(): void
    {
        $builder = $this->createLifecycleBuilder();
        Allure::setLifecycleBuilder($builder);
        self::assertSame($builder, Allure::getConfig());
    }

    public function testGetLifecycle_BuilderProvidesLifecycle_ReturnsSameLifecycle(): void
    {
        $builder = $this->createMock(LifecycleBuilderInterface::class);
        $resultsWriter = $this->createMock(ResultsWriterInterface::class);
        $builder
            ->method('createResultsWriter')
            ->willReturn($resultsWriter);
        $lifecycle = $this->createStub(AllureLifecycleInterface::class);
        Allure::setLifecycleBuilder($builder);
        $builder
            ->expects(self::once())
            ->method('createLifecycle')
            ->with(self::identicalTo($resultsWriter))
            ->willReturn($lifecycle);
        self::assertSame($lifecycle, Allure::getLifecycle());
    }

    public function testAddStep_ResultFactoryProvidesStep_LifecycleStartsAndStopsSameStep(): void
    {
        $step = new StepResult('a');
        $lifecycle = $this->createMock(AllureLifecycleInterface::class);
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithStep($step),
                $lifecycle,
            ),
        );
        $lifecycle
            ->expects(self::once())
            ->id('start')
            ->method('startStep')
            ->with(self::identicalTo($step));
        $lifecycle
            ->expects(self::once())
            ->after('start')
            ->method('stopStep')
            ->with(self::identicalTo('a'));
        Allure::addStep('b');
    }

    public function testAddStep_GivenName_StepHasSameName(): void
    {
        $step = new StepResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder($this->createResultFactoryWithStep($step)),
        );
        Allure::addStep('b');
        self::assertSame('b', $step->getName());
    }

    public function testAddStep_NoGivenStatus_StepHasPassedStatus(): void
    {
        $step = new StepResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder($this->createResultFactoryWithStep($step)),
        );
        Allure::addStep('b');
        self::assertSame(Status::passed(), $step->getStatus());
    }

    public function testAddStep_GivenStatus_StepHasSameStatus(): void
    {
        $step = new StepResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder($this->createResultFactoryWithStep($step)),
        );
        $status = Status::failed();
        Allure::addStep('b', $status);
        self::assertSame($status, $step->getStatus());
    }

    /**
     * @throws Throwable
     */
    public function testRunStep_NoExceptionThrownDuringStep_LifecycleStartsAndStopsStep(): void
    {
        $step = new StepResult('a');
        $lifecycle = $this->createMock(AllureLifecycleInterface::class);
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithStep($step),
                $lifecycle,
            ),
        );
        $lifecycle
            ->expects(self::once())
            ->id('start')
            ->method('startStep')
            ->with(self::identicalTo($step));
        $lifecycle
            ->expects(self::once())
            ->after('start')
            ->method('stopStep')
            ->with(self::identicalTo('a'));
        Allure::runStep(fn () => null);
    }

    /**
     * @throws Throwable
     */
    public function testRunStep_ExceptionThrownDuringStep_LifecycleStartsAndStopsStepAndThrowsSameException(): void
    {
        $step = new StepResult('a');
        $lifecycle = $this->createMock(AllureLifecycleInterface::class);
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithStep($step),
                $lifecycle,
            ),
        );
        $lifecycle
            ->expects(self::once())
            ->id('start')
            ->method('startStep')
            ->with(self::identicalTo($step));
        $lifecycle
            ->expects(self::once())
            ->after('start')
            ->method('stopStep')
            ->with(self::identicalTo('a'));
        $error = new Exception('c');
        $this->expectExceptionObject($error);
        Allure::runStep(fn () => throw $error);
    }

    public function testRunStep_NoNameNorDisplayNameAttributeProvided_StepHasDefaultName(): void
    {
        $step = new StepResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithStep($step),
                $this->createLifecycleWithUpdatableStep($step),
            ),
        );

        Allure::runStep(fn () => null);
        self::assertSame('step', $step->getName());
    }

    public function testRunStep_NoNameNorDisplayNameAttributeProvidedButDefaultNameIsSet_StepHasMatchingName(): void
    {
        $step = new StepResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithStep($step),
                $this->createLifecycleWithUpdatableStep($step),
            ),
        );

        Allure::setDefaultStepName('b');
        Allure::runStep(fn () => null);
        self::assertSame('b', $step->getName());
    }

    public function testRunStep_OnlyClosureDisplayNameAttributeProvided_StepHasProvidedName(): void
    {
        $step = new StepResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithStep($step),
                $this->createLifecycleWithUpdatableStep($step),
            ),
        );

        Allure::runStep(#[DisplayName('b')] fn () => null);
        self::assertSame('b', $step->getName());
    }

    public function testRunStep_OnlyMethodDisplayNameAttributeProvided_StepHasMatchingName(): void
    {
        $step = new StepResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithStep($step),
                $this->createLifecycleWithUpdatableStep($step),
            ),
        );

        Allure::runStep([$this, 'titledStep']);
        self::assertSame('b', $step->getName());
    }

    #[DisplayName('b')]
    public function titledStep(): void
    {
    }

    public function testRunStep_BothNameAndDisplayNameAttributeProvided_StepHasMatchingName(): void
    {
        $step = new StepResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithStep($step),
                $this->createLifecycleWithUpdatableStep($step),
            ),
        );

        Allure::runStep(#[DisplayName('b')] fn () => null, 'c');
        self::assertSame('c', $step->getName());
    }

    public function testRunStep_ClosureParameterAttributeProvided_StepHasMatchingParameter(): void
    {
        $step = new StepResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithStep($step),
                $this->createLifecycleWithUpdatableStep($step),
            ),
        );

        Allure::runStep(#[Parameter('b', 'c')] fn () => null);
        $parameter = $step->getParameters()[0] ?? null;
        self::assertSame('b', $parameter?->getName());
        self::assertSame('c', $parameter?->getValue());
    }

    public function testRunStep_MethodParameterAttributeProvided_StepHasMatchingParameter(): void
    {
        $step = new StepResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithStep($step),
                $this->createLifecycleWithUpdatableStep($step),
            ),
        );

        Allure::runStep([$this, 'parametrizedStep']);
        $parameter = $step->getParameters()[0] ?? null;
        self::assertSame('b', $parameter?->getName());
        self::assertSame('c', $parameter?->getValue());
    }

    #[Parameter('b', 'c')]
    public function parametrizedStep(): void
    {
    }

    /**
     * @dataProvider providerRunStepResult
     */
    public function testRunStep_StepReturnsValue_ReturnsSameValue(mixed $value): void
    {
        $step = new StepResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder($this->createResultFactoryWithStep($step)),
        );

        self::assertSame($value, Allure::runStep(fn (): mixed => $value, 'c'));
    }

    public function testRunStep_NoExceptionThrownDuringStep_StepStatusIsPassed(): void
    {
        $step = new StepResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithStep($step),
                $this->createLifecycleWithUpdatableStep($step),
            ),
        );

        Allure::runStep(fn () => null);
        self::assertSame(Status::passed(), $step->getStatus());
        self::assertNull($step->getStatusDetails());
    }

    public function testRunStep_ExceptionThrownDuringStep_StepStatusIsProvidedByDetector(): void
    {
        $step = new StepResult('a');
        $statusDetector = $this->createMock(StatusDetectorInterface::class);
        $status = Status::failed();
        $error = new Exception();
        $statusDetector
            ->method('getStatus')
            ->with(self::identicalTo($error))
            ->willReturn($status);
        $statusDetails = new StatusDetails();
        $statusDetector
            ->method('getStatusDetails')
            ->with(self::identicalTo($error))
            ->willReturn($statusDetails);

        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithStep($step),
                $this->createLifecycleWithUpdatableStep($step),
                $statusDetector,
            ),
        );

        try {
            Allure::runStep(fn() => throw $error);
        } catch (Throwable) {
        }
        self::assertSame($status, $step->getStatus());
        self::assertSame($statusDetails, $step->getStatusDetails());
    }

    /**
     * @return iterable<string, array{mixed}>
     */
    public static function providerRunStepResult(): iterable
    {
        return [
            'Null' => [null],
            'Integer' => [1],
            'Float' => [1.2],
            'String' => ['a'],
            'Boolean' => [false],
            'Array' => [['a' => 'b']],
            'Object' => [(object) ['a' => 'b']],
            'Resource' => [STDOUT],
            'Callable' => [fn () => null],
        ];
    }

    public function testRunStep_ResultFactoryProvidesStep_CallbackReceivesContextAttachedToSameStep(): void
    {
        $step = new StepResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithStep($step),
                $this->createLifecycleWithUpdatableStep($step),
            ),
        );

        Allure::runStep(fn (StepContextInterface $s) => $s->name('b'));
        self::assertSame('b', $step->getName());
    }

    /**
     * @dataProvider providerAttachmentProperties
     */
    public function testAttachment_ResultFactoryProvidesAttachment_AttachmentHasGivenProperties(
        string $name,
        ?string $type,
        ?string $fileExtension,
    ): void {
        $attachment = new AttachmentResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder($this->createResultFactoryWithAttachment($attachment)),
        );

        Allure::attachment($name, 'b', $type, $fileExtension);
        self::assertSame($name, $attachment->getName());
        self::assertSame($type, $attachment->getType());
        self::assertSame($fileExtension, $attachment->getFileExtension());
    }

    /**
     * @return iterable<string, array{string, string|null, string|null}>
     */
    public static function providerAttachmentProperties(): iterable
    {
        return [
            'Only name' => ['c', null, null],
            'Name and type' => ['c', 'd', null],
            'Name and file extension' => ['c', null, 'd'],
            'Name, type and file extension' => ['c', 'd', 'e'],
        ];
    }

    public function testAttachment_ResultFactoryProvidesAttachment_AttachmentIsAddedToLifecycle(): void
    {
        $attachment = new AttachmentResult('a');
        $lifecycle = $this->createMock(AllureLifecycleInterface::class);
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithAttachment($attachment),
                $lifecycle,
            ),
        );

        $lifecycle
            ->expects(self::once())
            ->method('addAttachment')
            ->with(
                self::identicalTo($attachment),
                self::isInstanceOf(StringDataSource::class),
            );
        Allure::attachment('b', 'c');
    }

    /**
     * @dataProvider providerAttachmentProperties
     */
    public function testAttachmentFile_ResultFactoryProvidesAttachment_AttachmentHasGivenProperties(
        string $name,
        ?string $type,
        ?string $fileExtension,
    ): void {
        $attachment = new AttachmentResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder($this->createResultFactoryWithAttachment($attachment)),
        );

        Allure::attachmentFile($name, 'b', $type, $fileExtension);
        self::assertSame($name, $attachment->getName());
        self::assertSame($type, $attachment->getType());
        self::assertSame($fileExtension, $attachment->getFileExtension());
    }

    public function testAttachmentFile_ResultFactoryProvidesAttachment_AttachmentIsAddedToLifecycle(): void
    {
        $attachment = new AttachmentResult('a');
        $lifecycle = $this->createMock(AllureLifecycleInterface::class);
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithAttachment($attachment),
                $lifecycle,
            ),
        );

        $lifecycle
            ->expects(self::once())
            ->method('addAttachment')
            ->with(
                self::identicalTo($attachment),
                self::isInstanceOf(StreamDataSource::class),
            );
        Allure::attachmentFile('b', 'c');
    }

    public function testEpic_GivenValue_TestHasMatchingLabel(): void
    {
        $test = new TestResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithTest($test),
                $this->createLifecycleWithUpdatableTest($test),
            ),
        );

        Allure::epic('b');
        $label = $test->getLabels()[0] ?? null;
        self::assertSame('epic', $label?->getName());
        self::assertSame('b', $label?->getValue());
    }

    public function testFeature_GivenValue_TestHasMatchingLabel(): void
    {
        $test = new TestResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithTest($test),
                $this->createLifecycleWithUpdatableTest($test),
            ),
        );

        Allure::feature('b');
        $label = $test->getLabels()[0] ?? null;
        self::assertSame('feature', $label?->getName());
        self::assertSame('b', $label?->getValue());
    }

    public function testStory_GivenValue_TestHasMatchingLabel(): void
    {
        $test = new TestResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithTest($test),
                $this->createLifecycleWithUpdatableTest($test),
            ),
        );

        Allure::story('b');
        $label = $test->getLabels()[0] ?? null;
        self::assertSame('story', $label?->getName());
        self::assertSame('b', $label?->getValue());
    }

    public function testSuite_GivenValue_TestHasMatchingLabel(): void
    {
        $test = new TestResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithTest($test),
                $this->createLifecycleWithUpdatableTest($test),
            ),
        );

        Allure::suite('b');
        $label = $test->getLabels()[0] ?? null;
        self::assertSame('suite', $label?->getName());
        self::assertSame('b', $label?->getValue());
    }

    public function testParentSuite_GivenValue_TestHasMatchingLabel(): void
    {
        $test = new TestResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithTest($test),
                $this->createLifecycleWithUpdatableTest($test),
            ),
        );

        Allure::parentSuite('b');
        $label = $test->getLabels()[0] ?? null;
        self::assertSame('parentSuite', $label?->getName());
        self::assertSame('b', $label?->getValue());
    }

    public function testSubSuite_GivenValue_TestHasMatchingLabel(): void
    {
        $test = new TestResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithTest($test),
                $this->createLifecycleWithUpdatableTest($test),
            ),
        );

        Allure::subSuite('b');
        $label = $test->getLabels()[0] ?? null;
        self::assertSame('subSuite', $label?->getName());
        self::assertSame('b', $label?->getValue());
    }

    public function testSeverity_GivenValue_TestHasMatchingLabel(): void
    {
        $test = new TestResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithTest($test),
                $this->createLifecycleWithUpdatableTest($test),
            ),
        );

        Allure::severity(Severity::critical());
        $label = $test->getLabels()[0] ?? null;
        self::assertSame('severity', $label?->getName());
        self::assertSame('critical', $label?->getValue());
    }

    public function testTag_GivenValue_TestHasMatchingLabel(): void
    {
        $test = new TestResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithTest($test),
                $this->createLifecycleWithUpdatableTest($test),
            ),
        );

        Allure::tag('b');
        $label = $test->getLabels()[0] ?? null;
        self::assertSame('tag', $label?->getName());
        self::assertSame('b', $label?->getValue());
    }

    public function testOwner_GivenValue_TestHasMatchingLabel(): void
    {
        $test = new TestResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithTest($test),
                $this->createLifecycleWithUpdatableTest($test),
            ),
        );

        Allure::owner('c');
        $label = $test->getLabels()[0] ?? null;
        self::assertSame('owner', $label?->getName());
        self::assertSame('c', $label?->getValue());
    }

    public function testLead_GivenValue_TestHasMatchingLabel(): void
    {
        $test = new TestResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithTest($test),
                $this->createLifecycleWithUpdatableTest($test),
            ),
        );

        Allure::lead('b');
        $label = $test->getLabels()[0] ?? null;
        self::assertSame('lead', $label?->getName());
        self::assertSame('b', $label?->getValue());
    }

    public function testPackage_GivenValue_TestHasMatchingLabel(): void
    {
        $test = new TestResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithTest($test),
                $this->createLifecycleWithUpdatableTest($test),
            ),
        );

        Allure::package('b');
        $label = $test->getLabels()[0] ?? null;
        self::assertSame('package', $label?->getName());
        self::assertSame('b', $label?->getValue());
    }

    public function testLabel_GivenNameAndValue_TestHasMatchingLabel(): void
    {
        $test = new TestResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithTest($test),
                $this->createLifecycleWithUpdatableTest($test),
            ),
        );

        Allure::label('b', 'c');
        $label = $test->getLabels()[0] ?? null;
        self::assertSame('b', $label?->getName());
        self::assertSame('c', $label?->getValue());
    }

    public function testParameter_GivenName_TestHasParameterWithSameName(): void
    {
        $test = new TestResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithTest($test),
                $this->createLifecycleWithUpdatableTest($test),
            ),
        );

        Allure::parameter('b', null);
        $parameter = $test->getParameters()[0] ?? null;
        self::assertSame('b', $parameter?->getName());
    }

    /**
     * @dataProvider providerParameterValue
     */
    public function testParameter_GivenValue_TestHasParameterWithSameValue(?string $value): void
    {
        $test = new TestResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithTest($test),
                $this->createLifecycleWithUpdatableTest($test),
            ),
        );

        Allure::parameter('b', $value);
        $parameter = $test->getParameters()[0] ?? null;
        self::assertNotNull($parameter);
        self::assertSame($value, $parameter->getValue());
    }

    /**
     * @return iterable<string, array{string|null}>
     */
    public static function providerParameterValue(): iterable
    {
        return [
            'Null value' => [null],
            'Non-null value' => ['c'],
        ];
    }

    public function testParameter_NotGivenExcludedFlag_TestHasParameterWithFalseExcludedFlag(): void
    {
        $test = new TestResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithTest($test),
                $this->createLifecycleWithUpdatableTest($test),
            ),
        );

        Allure::parameter('b', null);
        $parameter = $test->getParameters()[0] ?? null;
        self::assertFalse($parameter?->getExcluded());
    }

    /**
     * @dataProvider providerParameterExcluded
     */
    public function testParameter_GivenExcludedFlag_TestHasParameterWithSameExcludedFlag(bool $excluded): void
    {
        $test = new TestResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithTest($test),
                $this->createLifecycleWithUpdatableTest($test),
            ),
        );

        Allure::parameter('b', null, $excluded);
        $parameter = $test->getParameters()[0] ?? null;
        self::assertNotNull($parameter);
        self::assertSame($excluded, $parameter->getExcluded());
    }

    /**
     * @return iterable<string, array{bool}>
     */
    public static function providerParameterExcluded(): iterable
    {
        return [
            'Excluded' => [true],
            'Not excluded' => [false],
        ];
    }

    /**
     * @dataProvider providerParameterMode
     */
    public function testParameter_GivenMode_TestHasParameterWithSameMode(?ParameterMode $mode): void
    {
        $test = new TestResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithTest($test),
                $this->createLifecycleWithUpdatableTest($test),
            ),
        );

        Allure::parameter('b', null, mode: $mode);
        $parameter = $test->getParameters()[0] ?? null;
        self::assertNotNull($parameter);
        self::assertSame($mode, $parameter->getMode());
    }

    /**
     * @return iterable<string, array{ParameterMode|null}>
     */
    public static function providerParameterMode(): iterable
    {
        return [
            'Null mode' => [null],
            'Non-null mode' => [ParameterMode::hidden()],
        ];
    }

    public function testIssue_GivenNameWithValue_TestHasMatchingLink(): void
    {
        $test = new TestResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithTest($test),
                $this->createLifecycleWithUpdatableTest($test),
            ),
        );

        Allure::issue('b', 'c');
        $link = $test->getLinks()[0] ?? null;
        self::assertNotNull($link);
        self::assertSame(LinkType::issue(), $link->getType());
        self::assertSame('b', $link->getName());
        self::assertSame('c', $link->getUrl());
    }

    public function testIssue_GivenNameWithoutValue_TestHasMatchingLink(): void
    {
        $test = new TestResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithTest($test),
                $this->createLifecycleWithUpdatableTest($test),
            ),
        );

        Allure::issue('b');
        $link = $test->getLinks()[0] ?? null;
        self::assertNotNull($link);
        self::assertSame(LinkType::issue(), $link->getType());
        self::assertSame('b', $link->getName());
        self::assertNull($link->getUrl());
    }

    public function testTms_GivenNameWithValue_TestHasMatchingLink(): void
    {
        $test = new TestResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithTest($test),
                $this->createLifecycleWithUpdatableTest($test),
            ),
        );

        Allure::tms('b', 'c');
        $link = $test->getLinks()[0] ?? null;
        self::assertNotNull($link);
        self::assertSame(LinkType::tms(), $link->getType());
        self::assertSame('b', $link->getName());
        self::assertSame('c', $link->getUrl());
    }

    public function testTms_GivenNameWithoutValue_TestHasMatchingLink(): void
    {
        $test = new TestResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithTest($test),
                $this->createLifecycleWithUpdatableTest($test),
            ),
        );

        Allure::tms('b');
        $link = $test->getLinks()[0] ?? null;
        self::assertNotNull($link);
        self::assertSame(LinkType::tms(), $link->getType());
        self::assertSame('b', $link->getName());
        self::assertNull($link->getUrl());
    }

    public function testLink_GivenUrl_TestHasLinkWithSameUrl(): void
    {
        $test = new TestResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithTest($test),
                $this->createLifecycleWithUpdatableTest($test),
            ),
        );

        Allure::link('b');
        $link = $test->getLinks()[0] ?? null;
        self::assertSame('b', $link?->getUrl());
    }

    /**
     * @dataProvider providerLinkName
     */
    public function testLink_GivenName_TestHasLinkWithSameName(?string $name): void
    {
        $test = new TestResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithTest($test),
                $this->createLifecycleWithUpdatableTest($test),
            ),
        );

        Allure::link('b', $name);
        $link = $test->getLinks()[0] ?? null;
        self::assertNotNull($link);
        self::assertSame($name, $link->getName());
    }

    public function testLink_GivenNoType_TestHasLinkWithCustomType(): void
    {
        $test = new TestResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithTest($test),
                $this->createLifecycleWithUpdatableTest($test),
            ),
        );

        Allure::link('b');
        $link = $test->getLinks()[0] ?? null;
        self::assertSame(LinkType::custom(), $link?->getType());
    }

    public function testLink_GivenType_TestHasLinkWithSameType(): void
    {
        $test = new TestResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithTest($test),
                $this->createLifecycleWithUpdatableTest($test),
            ),
        );

        $type = LinkType::tms();
        Allure::link('b', type: $type);
        $link = $test->getLinks()[0] ?? null;
        self::assertSame($type, $link?->getType());
    }

    /**
     * @return iterable<string, array{string|null}>
     */
    public static function providerLinkName(): iterable
    {
        return [
            'Null name' => [null],
            'Non-null name' => ['c'],
        ];
    }

    public function testDisplayName_LifecycleUpdatesTest_TestHasSameName(): void
    {
        $test = new TestResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithTest($test),
                $this->createLifecycleWithUpdatableTest($test),
            ),
        );

        Allure::displayName('b');
        self::assertSame('b', $test->getName());
    }

    public function testTitle_LifecycleUpdatesStep_StepHasSameName(): void
    {
        $step = new StepResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithStep($step),
                $this->createLifecycleWithUpdatableStep($step),
            ),
        );

        Allure::displayName('b');
        self::assertSame('b', $step->getName());
    }

    public function testDescription_LifecycleUpdatesTest_TestHasSameDescription(): void
    {
        $test = new TestResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithTest($test),
                $this->createLifecycleWithUpdatableTest($test),
            ),
        );

        Allure::description('b');
        self::assertSame('b', $test->getDescription());
    }

    public function testDescription_LifecycleUpdatesStep_StepHasSameDescription(): void
    {
        $step = new StepResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithStep($step),
                $this->createLifecycleWithUpdatableStep($step),
            ),
        );

        Allure::description('b');
        self::assertSame('b', $step->getDescription());
    }

    public function testDescriptionHtml_LifecycleUpdatesTest_TestHasSameDescription(): void
    {
        $test = new TestResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithTest($test),
                $this->createLifecycleWithUpdatableTest($test),
            ),
        );

        Allure::descriptionHtml('b');
        self::assertSame('b', $test->getDescriptionHtml());
    }

    public function testDescriptionHtml_LifecycleUpdatesStep_StepHasSameDescription(): void
    {
        $step = new StepResult('a');
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithStep($step),
                $this->createLifecycleWithUpdatableStep($step),
            ),
        );

        Allure::descriptionHtml('b');
        self::assertSame('b', $step->getDescriptionHtml());
    }

    private function createLifecycleBuilder(
        ?ResultFactoryInterface $resultFactory = null,
        ?AllureLifecycleInterface $lifecycle = null,
        ?StatusDetectorInterface $statusDetector = null,
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
        if (isset($statusDetector)) {
            $builder
                ->method('getStatusDetector')
                ->willReturn($statusDetector);
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

    private function createResultFactoryWithAttachment(AttachmentResult $attachment): ResultFactoryInterface
    {
        $resultFactory = $this->createStub(ResultFactoryInterface::class);
        $resultFactory
            ->method('createAttachment')
            ->willReturn($attachment);

        return $resultFactory;
    }

    private function createResultFactoryWithTest(TestResult $test): ResultFactoryInterface
    {
        $resultFactory = $this->createStub(ResultFactoryInterface::class);
        $resultFactory
            ->method('createTest')
            ->willReturn($test);

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

    private function createLifecycleWithUpdatableTest(TestResult $test): AllureLifecycleInterface
    {
        $lifecycle = $this->createMock(AllureLifecycleInterface::class);
        $lifecycle
            ->method('updateTest')
            ->with(
                self::callback(
                    function (callable $callable) use ($test): bool {
                        $callable($test);

                        return true;
                    },
                ),
            );
        $lifecycle
            ->method('updateExecutionContext')
            ->with(
                self::callback(
                    function (callable $callable) use ($test): bool {
                        $callable($test);

                        return true;
                    },
                ),
            );

        return $lifecycle;
    }
}
