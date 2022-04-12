<?php

namespace Yandex\Allure\Adapter\Support;

use Exception;
use PHPUnit\Framework\TestCase;
use Yandex\Allure\Adapter\Allure;
use Yandex\Allure\Adapter\AllureException;
use Yandex\Allure\Adapter\Event\StepFailedEvent;
use Yandex\Allure\Adapter\Event\StepFinishedEvent;
use Yandex\Allure\Adapter\Event\StepStartedEvent;

class StepSupportTest extends TestCase
{
    use StepSupport;

    private const STEP_NAME = 'step-name';
    private const STEP_TITLE = 'step-title';

    /**
     * @var \Yandex\Allure\Adapter\Support\MockedLifecycle
     */
    private $mockedLifecycle;

    public function __construct()
    {
        $this->mockedLifecycle = new MockedLifecycle();
        parent::__construct();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockedLifecycle = new MockedLifecycle();
        $this->getMockedLifecycle()->reset();
        Allure::setLifecycle($this->getMockedLifecycle());
    }

    public function testExecuteStepCorrectly(): void
    {
        $logicWithNoException = function () {
            //We do nothing, hence no error
        };
        $this->executeStep(self::STEP_NAME, $logicWithNoException, self::STEP_TITLE);
        $events = $this->getMockedLifecycle()->getEvents();
        $this->assertEquals(2, sizeof($events));
        $this->assertTrue($events[0] instanceof StepStartedEvent);
        $this->assertTrue($events[1] instanceof StepFinishedEvent);
    }

    public function testExecuteFailingStep()
    {
        $logicWithException = function () {
            throw new Exception();
        };
        $this->expectException(Exception::class);
        $this->executeStep(self::STEP_NAME, $logicWithException, self::STEP_TITLE);
        $events = $this->getMockedLifecycle()->getEvents();
        $this->assertEquals(3, sizeof($events));
        $this->assertTrue($events[0] instanceof StepStartedEvent);
        $this->assertTrue($events[1] instanceof StepFailedEvent);
        $this->assertTrue($events[2] instanceof StepFinishedEvent);
    }

    public function testExecuteStepWithMissingData()
    {
        $this->expectException(AllureException::class);
        $this->executeStep(null, null, null);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Allure::setDefaultLifecycle();
    }

    private function getMockedLifecycle(): MockedLifecycle
    {
        return $this->mockedLifecycle;
    }
}
