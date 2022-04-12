<?php

namespace Yandex\Allure\Adapter\Event\Storage;

use Yandex\Allure\Adapter\Model\TestSuite;

class TestSuiteStorage
{
    /**
     * @var array
     */
    private $storage;

    public function __construct()
    {
        $this->clear();
    }

    /**
     * @param string $uuid
     * @return TestSuite
     */
    public function get($uuid)
    {
        if (!array_key_exists($uuid, $this->storage)) {
            $this->storage[$uuid] = new TestSuite();
        }

        return $this->storage[$uuid];
    }

    public function remove($uuid)
    {
        if (array_key_exists($uuid, $this->storage)) {
            unset($this->storage[$uuid]);
        }
    }

    public function clear()
    {
        $this->storage = [];
    }

    public function isEmpty()
    {
        return $this->size() === 0;
    }

    public function size()
    {
        return sizeof($this->storage);
    }
}
