<?php

namespace Qameta\Allure\Test\Model;

use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Qameta\Allure\Allure;
use Qameta\Allure\Attribute\DisplayName;
use Qameta\Allure\Attribute\Parameter as AttrParameter;
use Qameta\Allure\Io\ClockInterface;
use Qameta\Allure\Model\Label;
use Qameta\Allure\Model\LinkType;
use Qameta\Allure\Model\Parameter;
use Qameta\Allure\Model\Severity;
use Qameta\Allure\Model\Status;
use Qameta\Allure\Model\StatusDetails;
use Qameta\Allure\Setup\LinkTemplate;
use Qameta\Allure\StepContextInterface;
use RuntimeException;
use Throwable;

class TemporaryTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Allure::reset();
        Allure::getLifecycleConfigurator()->setOutputDirectory(__DIR__ . '/../../build/allure');
    }

    /**
     * @throws Throwable
     */
    public function testLifecycle(): void
    {
        $this->expectNotToPerformAssertions();

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->method('error')
            ->willReturnCallback(
                fn (string $message) => throw new RuntimeException($message),
            );

        $clock = $this->createMock(ClockInterface::class);
        $time = new DateTime('@0');
        $clock
            ->method('now')
            ->willReturnCallback(
                fn (): DateTimeImmutable => DateTimeImmutable::createFromMutable(
                    $time->modify('+1 second')->modify('+1 millisecond'),
                ),
            );
        Allure::getLifecycleConfigurator()
            ->setLogger($logger)
            ->setClock($clock)
            ->addLinkTemplate(LinkType::issue(), new LinkTemplate('https://example.org/issue/%s'));
        $resultFactory = Allure::getConfig()->getResultFactory();
        $lifecycle = Allure::getLifecycle();
        $container = $resultFactory->createContainer();
        $lifecycle->startContainer($container);

        $setupFixture = $resultFactory
            ->createFixture()
            ->setName('Setup fixture')
            ->setStatus(Status::failed());
        $lifecycle->startBeforeFixture($setupFixture, $container->getUuid());
        $lifecycle->stopFixture($setupFixture->getUuid());
        $test = $resultFactory
            ->createTest()
            ->setHistoryId('history-id')
            ->setTestCaseId('test-case-id')
            ->setName('Test name')
            ->setFullName('Full test name')
            ->setStatus(Status::failed())
            ->setStatusDetails(
                (new StatusDetails())
                    ->makeFlaky(true)
                    ->makeKnown(true)
                    ->makeMuted(false)
                    ->setMessage('Test status details message')
                    ->setTrace('Test status details trace')
            )
            ->addLabels(
                Label::id('allure-id'),
                Label::thread('Thread label'),
                Label::testMethod('testMethod'),
            );
        $lifecycle->scheduleTest($test, $container->getUuid());
        $lifecycle->startTest($test->getUuid());

        Allure::owner('Owner label');
        Allure::lead('Lead label');
        Allure::label('Label name', 'Label value');
        Allure::severity(Severity::critical());
        Allure::parameter('Test param1 name', 'Test param1 value');
        Allure::parameter('Test param2 name', null);
        Allure::suite('Suite label');
        Allure::parentSuite('Parent suite label');
        Allure::subSuite('Sub-suite label');
        Allure::tag('Tag label');
        Allure::package('Package label');
        Allure::epic('Epic label');
        Allure::feature('Feature label');
        Allure::feature('Another feature label');
        Allure::story('Story label');
        Allure::issue('C123');
        Allure::tms('TMS', 'https://example.com');
        Allure::link('Custom', 'https://example.com');
        Allure::description('Test description');
        Allure::descriptionHtml('<a href="#">Test HTML description</a>');
        Allure::attachmentFile('Attachment1 name', __FILE__);
        Allure::attachmentFile('Attachment2 name', __FILE__, 'text/plain', 'txt');

        Allure::runStep(
            #[DisplayName('Step 1 attribute')]
            #[AttrParameter('foo', 'bar')]
            function (StepContextInterface $step): void {
                $step->parameter('Step 1 param', 'xxx');
                Allure::descriptionHtml('<a href="#">Step HTML description</a>');
                Allure::attachment(
                    'Attachment3 name',
                    'foo',
                    'text/plain',
                    'txt',
                );
            },
        );
        Allure::runStep([$this, 'step']);

        $secondStep = $resultFactory
            ->createStep()
            ->setName('Step 2 name')
            ->setStatus(Status::passed())
            ->setDescriptionHtml('<a href="#">Step description</a>')
            ->setParameters(
                (new Parameter('Step parameter'))->setValue('Step parameter value'),
            );
        $lifecycle->startStep($secondStep);
        $nestedStep = $resultFactory
            ->createStep()
            ->setName('Nested step')
            ->setStatus(Status::skipped())
            ->setParameters(
                (new Parameter('Nested step parameter'))->setValue('value'),
            );
        $lifecycle->startStep($nestedStep);
        $lifecycle->stopStep($nestedStep->getUuid());
        $lifecycle->stopStep($secondStep->getUuid());
        $lifecycle->stopTest($test->getUuid());
        $lifecycle->writeTest($test->getUuid());
        $lifecycle->stopContainer($container->getUuid());
        $lifecycle->writeContainer($container->getUuid());
    }

    #[DisplayName('Method step')]
    public function step(StepContextInterface $context): void
    {
        $context->parameter('baz', 'bar');
    }
}
