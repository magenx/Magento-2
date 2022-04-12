<?php

namespace Yandex\Allure\Adapter\Support;

use Exception;
use Yandex\Allure\Adapter\Allure;
use Yandex\Allure\Adapter\AllureException;
use Yandex\Allure\Adapter\Event\StepFailedEvent;
use Yandex\Allure\Adapter\Event\StepFinishedEvent;
use Yandex\Allure\Adapter\Event\StepStartedEvent;

const STEP_LOGIC_KEY = 'logic';
const STEP_TITLE_KEY = 'title';
const STEP_CHILD_STEPS_KEY = 'childSteps';

/**
 * Use this trait in order to add Allure steps support
 * @package Yandex\Allure\Adapter\Support
 */
trait StepSupport
{

    use Utils;

    /**
     * Adds a simple step to current test case
     * @param string $name step name
     * @param callable $logic anonymous function containing the entire step logic.
     * @param string $title an optional title for the step
     * @return mixed
     * @throws \Yandex\Allure\Adapter\AllureException
     * @throws \Exception
     */
    public function executeStep($name, $logic, $title = null)
    {
        $logicResult = null;

        if (isset($name) && is_callable($logic)) {
            $event = new StepStartedEvent($name);
            if (isset($title)) {
                $event->withTitle($title);
            } else {
                $event->withTitle($name);
            }
            Allure::lifecycle()->fire($event);
            try {
                $logicResult = $logic();
                Allure::lifecycle()->fire(new StepFinishedEvent());
            } catch (Exception $e) {
                $stepFailedEvent = new StepFailedEvent();
                Allure::lifecycle()->fire($stepFailedEvent);
                Allure::lifecycle()->fire(new StepFinishedEvent());
                throw $e;
            }
        } else {
            throw new AllureException("Step name shouldn't be null and logic should be a callable.");
        }

        return $logicResult;
    }
}
