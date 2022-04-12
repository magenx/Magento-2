<?php

namespace Yandex\Allure\Adapter\Support;

use Yandex\Allure\Adapter\Allure;
use Yandex\Allure\Adapter\Event\Event;

/**
 * All events are collected to the array and returned
 * @package Yandex\Allure\Adapter\Support
 */
class MockedLifecycle extends Allure
{
    private $events;

    public function __construct()
    {
        parent::__construct();
        $this->reset();
    }

    public function fire(Event $event)
    {
        $this->events[] = $event;
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    public function reset()
    {
        $this->events = array();
    }
}
