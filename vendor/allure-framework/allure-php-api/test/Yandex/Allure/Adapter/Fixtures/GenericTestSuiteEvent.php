<?php

namespace Yandex\Allure\Adapter\Fixtures;

use Yandex\Allure\Adapter\Event\TestSuiteEvent;
use Yandex\Allure\Adapter\Model\Entity;
use Yandex\Allure\Adapter\Model\TestSuite;
use Yandex\Allure\Adapter\AllureTest;

class GenericTestSuiteEvent implements TestSuiteEvent
{
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function process(Entity $context)
    {
        if ($context instanceof TestSuite) {
            $context->setName($this->name);
        }
    }

    public function getUuid()
    {
        return AllureTest::TEST_SUITE_UUID;
    }
}
