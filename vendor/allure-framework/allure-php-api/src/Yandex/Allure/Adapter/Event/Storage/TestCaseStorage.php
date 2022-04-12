<?php

namespace Yandex\Allure\Adapter\Event\Storage;

use Yandex\Allure\Adapter\Model\TestCase;

class TestCaseStorage
{
    /**
     * @var TestCase
     */
    private $case;

    /**
     * @return TestCase
     */
    public function get()
    {
        if (!isset($this->case)) {
            $this->case = new TestCase();
        }

        return $this->case;
    }

    /**
     * @param TestCase $case
     */
    public function put(TestCase $case)
    {
        $this->case = $case;
    }

    public function clear()
    {
        unset($this->case);
    }

    public function isEmpty()
    {
        return !isset($this->case);
    }
}
