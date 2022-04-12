<?php

namespace Yandex\Allure\Adapter\Event\Storage\Fixtures;

use Yandex\Allure\Adapter\Event\Storage\StepStorage;
use Yandex\Allure\Adapter\Model\Step;

class MockedRootStepStorage extends StepStorage
{
    protected function getRootStep()
    {
        $rootStep = new Step();
        $rootStep->setName('root-step');

        return $rootStep;
    }
}
