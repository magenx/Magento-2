<?php

namespace Yandex\Allure\Adapter\Event\Storage;

use Yandex\Allure\Adapter\Model\Status;
use Yandex\Allure\Adapter\Model\Step;
use \SplStack;
use Yandex\Allure\Adapter\Support\Utils;

class StepStorage
{
    use Utils;

    const ROOT_STEP_NAME = 'root-step';

    /**
     * @var SplStack
     */
    private $storage;

    public function __construct()
    {
        $this->storage = new SplStack();
    }

    /**
     * @return Step
     */
    public function getLast()
    {
        if ($this->storage->isEmpty()) {
            $this->put($this->getRootStep());
        }

        return $this->storage->top();
    }

    /**
     * @return Step
     */
    public function pollLast()
    {
        $step = $this->storage->pop();
        if ($this->storage->isEmpty()) {
            $this->storage->push($this->getRootStep());
        }

        return $step;
    }

    /**
     * @param Step $step
     */
    public function put(Step $step)
    {
        $this->storage->push($step);
    }

    public function clear()
    {
        $this->storage = new SplStack();
        $this->put($this->getRootStep());
    }

    public function isEmpty()
    {
        return ($this->size() === 0) && $this->isRootStep($this->getLast());
    }

    public function size()
    {
        return $this->storage->count() - 1;
    }

    public function isRootStep(Step $step)
    {
        return $step->getName() === self::ROOT_STEP_NAME;
    }

    /**
     * @return Step
     */
    protected function getRootStep()
    {
        $step = new Step();
        $step->setName(self::ROOT_STEP_NAME);
        $step->setTitle(
            "If you're seeing this then there's an error in step processing. "
            . "Please send feedback to allure@yandex-team.ru. Thank you."
        );
        $step->setStart(self::getTimestamp());
        $step->setStatus(Status::BROKEN);

        return $step;
    }
}
