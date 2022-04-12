<?php

namespace Yandex\Allure\Adapter;

use Yandex\Allure\Adapter\Event\ClearStepStorageEvent;
use Yandex\Allure\Adapter\Event\ClearTestCaseStorageEvent;
use Yandex\Allure\Adapter\Event\Event;
use Yandex\Allure\Adapter\Event\StepEvent;
use Yandex\Allure\Adapter\Event\StepFinishedEvent;
use Yandex\Allure\Adapter\Event\StepStartedEvent;
use Yandex\Allure\Adapter\Event\Storage\StepStorage;
use Yandex\Allure\Adapter\Event\Storage\TestCaseStorage;
use Yandex\Allure\Adapter\Event\Storage\TestSuiteStorage;
use Yandex\Allure\Adapter\Event\TestCaseEvent;
use Yandex\Allure\Adapter\Event\TestCaseFinishedEvent;
use Yandex\Allure\Adapter\Event\TestCaseStartedEvent;
use Yandex\Allure\Adapter\Event\TestSuiteEvent;
use Yandex\Allure\Adapter\Event\TestSuiteFinishedEvent;
use Yandex\Allure\Adapter\Model\Provider;
use Yandex\Allure\Adapter\Model\Step;
use Yandex\Allure\Adapter\Model\TestSuite;
use Yandex\Allure\Adapter\Support\Utils;

class Allure
{
    use Utils;

    /**
     * @var Allure
     */
    private static $lifecycle;

    private $stepStorage;
    private $testCaseStorage;
    private $testSuiteStorage;

    /**
     * @var Event
     */
    private $lastEvent;

    protected function __construct()
    {
        $this->stepStorage = new StepStorage();
        $this->testCaseStorage = new TestCaseStorage();
        $this->testSuiteStorage = new TestSuiteStorage();
    }

    /**
     * @return Allure
     */
    public static function lifecycle()
    {
        if (!isset(self::$lifecycle)) {
            self::setDefaultLifecycle();
        }

        return self::$lifecycle;
    }

    public static function setLifecycle(Allure $lifecycle)
    {
        self::$lifecycle = $lifecycle;
    }

    public static function setDefaultLifecycle()
    {
        self::$lifecycle = new Allure();
    }

    public function fire(Event $event)
    {
        if ($event instanceof StepStartedEvent) {
            $this->processStepStartedEvent($event);
        } elseif ($event instanceof StepFinishedEvent) {
            $this->processStepFinishedEvent($event);
        } elseif ($event instanceof TestCaseStartedEvent) {
            $this->processTestCaseStartedEvent($event);
        } elseif ($event instanceof TestCaseFinishedEvent) {
            $this->processTestCaseFinishedEvent($event);
        } elseif ($event instanceof TestSuiteFinishedEvent) {
            $this->processTestSuiteFinishedEvent($event);
        } elseif ($event instanceof TestSuiteEvent) {
            $this->processTestSuiteEvent($event);
        } elseif ($event instanceof ClearStepStorageEvent) {
            $this->processClearStepStorageEvent();
        } elseif ($event instanceof ClearTestCaseStorageEvent) {
            $this->processClearTestCaseStorageEvent();
        } elseif ($event instanceof TestCaseEvent) {
            $this->processTestCaseEvent($event);
        } elseif ($event instanceof StepEvent) {
            $this->processStepEvent($event);
        } else {
            throw new AllureException("Unknown event: " . get_class($event));
        }
        $this->lastEvent = $event;
    }

    protected function processStepStartedEvent(StepStartedEvent $event)
    {
        $step = new Step();
        $event->process($step);
        $this->getStepStorage()->put($step);
    }

    protected function processStepFinishedEvent(StepFinishedEvent $event)
    {
        $step = $this->getStepStorage()->pollLast();
        $event->process($step);
        $this->getStepStorage()->getLast()->addStep($step);
    }

    protected function processStepEvent(StepEvent $event)
    {
        $step = $this->getStepStorage()->getLast();
        $event->process($step);
    }

    protected function processTestCaseStartedEvent(TestCaseStartedEvent $event)
    {
        //init root step if needed
        $this->getStepStorage()->getLast();

        $testCase = $this->getTestCaseStorage()->get();
        $event->process($testCase);
        $this->getTestSuiteStorage()->get($event->getSuiteUuid())->addTestCase($testCase);
    }

    protected function processTestCaseFinishedEvent(TestCaseFinishedEvent $event)
    {
        $testCase = $this->getTestCaseStorage()->get();
        $event->process($testCase);
        $rootStep = $this->getStepStorage()->pollLast();
        foreach ($rootStep->getSteps() as $step) {
            $testCase->addStep($step);
        }
        foreach ($rootStep->getAttachments() as $attachment) {
            $testCase->addAttachment($attachment);
        }
        $this->getTestCaseStorage()->clear();
    }

    protected function processTestCaseEvent(TestCaseEvent $event)
    {
        $testCase = $this->getTestCaseStorage()->get();
        $event->process($testCase);
    }

    protected function processTestSuiteEvent(TestSuiteEvent $event)
    {
        $uuid = $event->getUuid();
        $testSuite = $this->getTestSuiteStorage()->get($uuid);
        $event->process($testSuite);
    }

    protected function processTestSuiteFinishedEvent(TestSuiteFinishedEvent $event)
    {
        $suiteUuid = $event->getUuid();
        $testSuite = $this->getTestSuiteStorage()->get($suiteUuid);
        $event->process($testSuite);
        $this->getTestSuiteStorage()->remove($suiteUuid);
        $this->saveToFile($suiteUuid, $testSuite);
    }

    protected function saveToFile($testSuiteUuid, TestSuite $testSuite)
    {
        if ($testSuite->size() > 0) {
            $xml = $testSuite->serialize();
            $fileName = $testSuiteUuid . '-testsuite.xml';
            $filePath = Provider::getOutputDirectory() . DIRECTORY_SEPARATOR . $fileName;
            file_put_contents($filePath, $xml);
        }
    }

    protected function processClearStepStorageEvent()
    {
        $this->getStepStorage()->clear();
    }

    protected function processClearTestCaseStorageEvent()
    {
        $this->getTestCaseStorage()->clear();
    }

    /**
     * @return \Yandex\Allure\Adapter\Event\Storage\StepStorage
     */
    public function getStepStorage()
    {
        return $this->stepStorage;
    }

    /**
     * @return \Yandex\Allure\Adapter\Event\Storage\TestCaseStorage
     */
    public function getTestCaseStorage()
    {
        return $this->testCaseStorage;
    }

    /**
     * @return \Yandex\Allure\Adapter\Event\Storage\TestSuiteStorage
     */
    public function getTestSuiteStorage()
    {
        return $this->testSuiteStorage;
    }

    /**
     * @return \Yandex\Allure\Adapter\Event\Event
     */
    public function getLastEvent()
    {
        return $this->lastEvent;
    }
}
