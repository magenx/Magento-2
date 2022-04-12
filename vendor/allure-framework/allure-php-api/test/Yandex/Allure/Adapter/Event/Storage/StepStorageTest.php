<?php

namespace Yandex\Allure\Adapter\Event\Storage;

use PHPUnit\Framework\TestCase;
use Yandex\Allure\Adapter\Model\Step;

class StepStorageTest extends TestCase
{
    private const TEST_STEP_NAME = 'test-step';

    public function testEmptyStorage(): void
    {
        $storage = new Fixtures\MockedRootStepStorage();
        $this->assertTrue($storage->isRootStep($storage->getLast()));
        $this->assertTrue($storage->isRootStep($storage->pollLast()));
        $this->assertTrue($storage->isEmpty());
    }

    public function testNonEmptyStorage(): void
    {
        $storage = new Fixtures\MockedRootStepStorage();
        $step = new Step();
        $step->setName(self::TEST_STEP_NAME);
        $storage->put($step);
        $this->assertEquals(self::TEST_STEP_NAME, $storage->getLast()->getName());
    }
}
