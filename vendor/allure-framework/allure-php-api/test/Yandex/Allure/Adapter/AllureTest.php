<?php

namespace Yandex\Allure\Adapter;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Yandex\Allure\Adapter\Event\ClearStepStorageEvent;
use Yandex\Allure\Adapter\Event\ClearTestCaseStorageEvent;
use Yandex\Allure\Adapter\Event\StepFinishedEvent;
use Yandex\Allure\Adapter\Event\StepStartedEvent;
use Yandex\Allure\Adapter\Event\TestCaseFinishedEvent;
use Yandex\Allure\Adapter\Event\TestCaseStartedEvent;
use Yandex\Allure\Adapter\Event\TestSuiteFinishedEvent;
use Yandex\Allure\Adapter\Model\Attachment;
use Yandex\Allure\Adapter\Model\Provider;
use Yandex\Allure\Adapter\Model\Step;
use Yandex\Allure\Adapter\Model\TestCase;
use Yandex\Allure\Adapter\Fixtures\GenericStepEvent;
use Yandex\Allure\Adapter\Fixtures\GenericTestCaseEvent;
use Yandex\Allure\Adapter\Fixtures\GenericTestSuiteEvent;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;

class AllureTest extends PhpUnitTestCase
{
    const STEP_NAME = 'step-name';
    const TEST_CASE_NAME = 'test-case-name';
    const TEST_SUITE_NAME = 'test-suite-name';
    const TEST_SUITE_UUID = 'test-suite-uuid';
    const STEP_ATTACHMENT_TITLE = 'step-attachment-caption';
    const STEP_ATTACHMENT_SOURCE = 'step-attachment-source';
    const STEP_ATTACHMENT_TYPE = 'text/plain';

    public function testStepStorageClearEvent()
    {
        Allure::lifecycle()->getStepStorage()->clear();
        Allure::lifecycle()->getStepStorage()->put(new Step());
        Allure::lifecycle()->fire(new ClearStepStorageEvent());
        $this->assertTrue(Allure::lifecycle()->getStepStorage()->isEmpty());
    }

    public function testTestCaseStorageClear()
    {
        Allure::lifecycle()->getTestCaseStorage()->clear();
        Allure::lifecycle()->getTestCaseStorage()->put(new TestCase());
        Allure::lifecycle()->fire(new ClearTestCaseStorageEvent());
        $this->assertTrue(Allure::lifecycle()->getTestCaseStorage()->isEmpty());
    }

    public function testStepStartedEvent()
    {
        Allure::lifecycle()->getStepStorage()->clear();
        $this->assertTrue(Allure::lifecycle()->getStepStorage()->isEmpty());
        Allure::lifecycle()->fire(new StepStartedEvent(self::STEP_NAME));
        $this->assertEquals(1, Allure::lifecycle()->getStepStorage()->size());
        $step = Allure::lifecycle()->getStepStorage()->getLast();
        $this->assertEquals(self::STEP_NAME, $step->getName());
    }

    public function testStepFinishedEvent()
    {
        $step = new Step();
        $step->setName(self::STEP_NAME);
        Allure::lifecycle()->getStepStorage()->put($step);
        Allure::lifecycle()->fire(new StepFinishedEvent());
        $step = Allure::lifecycle()->getStepStorage()->getLast();
        $this->assertEquals(self::STEP_NAME, $step->getName());
    }

    public function testGenericStepEvent()
    {
        $step = new Step();
        Allure::lifecycle()->getStepStorage()->clear();
        Allure::lifecycle()->getStepStorage()->put($step);
        Allure::lifecycle()->fire(new GenericStepEvent(self::STEP_NAME));
        $this->assertEquals(self::STEP_NAME, $step->getName());
    }

    public function testTestCaseStarted()
    {
        Allure::lifecycle()->getTestCaseStorage()->clear();
        Allure::lifecycle()->getTestSuiteStorage()->clear();
        $this->assertTrue(Allure::lifecycle()->getTestCaseStorage()->isEmpty());
        Allure::lifecycle()->fire(new TestCaseStartedEvent(self::TEST_SUITE_UUID, self::TEST_CASE_NAME));
        $testCase = Allure::lifecycle()
            ->getTestSuiteStorage()
            ->get(self::TEST_SUITE_UUID)
            ->getTestCase(self::TEST_CASE_NAME);
        $this->assertNotEmpty($testCase);
        $this->assertEquals(self::TEST_CASE_NAME, $testCase->getName());
    }

    public function testTestCaseFinishedEvent()
    {
        Allure::lifecycle()->getStepStorage()->clear();
        Allure::lifecycle()->getStepStorage()->getLast(); //To initialize root step
        Allure::lifecycle()->getTestCaseStorage()->clear();
        $step = new Step();
        $step->setName(self::STEP_NAME);
        $attachment = new Attachment(
            self::STEP_ATTACHMENT_TITLE,
            self::STEP_ATTACHMENT_SOURCE,
            self::STEP_ATTACHMENT_TYPE
        );
        Allure::lifecycle()->getStepStorage()->getLast()->addStep($step);
        Allure::lifecycle()->getStepStorage()->getLast()->addAttachment($attachment);

        $testCaseFromStorage = Allure::lifecycle()->getTestCaseStorage()->get();

        Allure::lifecycle()->fire(new TestCaseFinishedEvent());

        //Checking that attachments were moved
        $attachments = $testCaseFromStorage->getAttachments();
        $this->assertEquals(1, sizeof($attachments));
        $attachment = array_pop($attachments);
        $this->assertTrue(
            ($attachment instanceof Attachment) &&
            ($attachment->getTitle() === self::STEP_ATTACHMENT_TITLE) &&
            ($attachment->getSource() === self::STEP_ATTACHMENT_SOURCE) &&
            ($attachment->getType() === self::STEP_ATTACHMENT_TYPE)
        );

        //Checking that steps were moved
        $steps = $testCaseFromStorage->getSteps();
        $this->assertEquals(1, sizeof($steps));
        $stepFromStorage = array_pop($steps);
        $this->assertTrue(
            ($stepFromStorage instanceof Step) &&
            ($stepFromStorage->getName() === self::STEP_NAME)
        );
        $this->assertTrue(Allure::lifecycle()->getTestCaseStorage()->isEmpty());
    }

    public function testGenericTestCaseEvent()
    {
        $testCase = new TestCase();
        Allure::lifecycle()->getTestCaseStorage()->clear();
        Allure::lifecycle()->getTestCaseStorage()->put($testCase);
        Allure::lifecycle()->fire(new GenericTestCaseEvent(self::TEST_CASE_NAME));
        $this->assertEquals(self::TEST_CASE_NAME, $testCase->getName());
    }

    public function testGenericTestSuiteEvent()
    {
        Allure::lifecycle()->getTestSuiteStorage()->clear();
        $event = new GenericTestSuiteEvent(self::TEST_SUITE_NAME);
        $testSuite = Allure::lifecycle()->getTestSuiteStorage()->get($event->getUuid());
        Allure::lifecycle()->fire($event);
        $this->assertEquals(self::TEST_SUITE_NAME, $testSuite->getName());
    }

    public function testTestSuiteFinishedEvent()
    {
        Allure::lifecycle()->getTestSuiteStorage()->clear();
        $testSuite = Allure::lifecycle()->getTestSuiteStorage()->get(self::TEST_SUITE_UUID);
        $testSuite->addTestCase(new TestCase());

        $this->assertEquals(1, Allure::lifecycle()->getTestSuiteStorage()->size());

        $outputDirectory = sys_get_temp_dir();
        AnnotationRegistry::registerAutoloadNamespace(
            'JMS\Serializer\Annotation',
            __DIR__ . "/../../../../vendor/jms/serializer/src"
        );

        Provider::setOutputDirectory($outputDirectory);
        $xmlFilePath = $outputDirectory . DIRECTORY_SEPARATOR . self::TEST_SUITE_UUID . '-testsuite.xml';

        Allure::lifecycle()->fire(new TestSuiteFinishedEvent(self::TEST_SUITE_UUID));
        $this->assertTrue(Allure::lifecycle()->getTestSuiteStorage()->isEmpty());
        $this->assertTrue(file_exists($xmlFilePath));
    }
}
