<?php

namespace Yandex\Allure\Adapter\Event;

use Yandex\Allure\Adapter\Model\Description;
use Yandex\Allure\Adapter\Model\Entity;
use Yandex\Allure\Adapter\Model\Status;
use Yandex\Allure\Adapter\Model\TestCase;
use Yandex\Allure\Adapter\Support\Utils;

class TestCaseStartedEvent implements TestCaseEvent
{
    use Utils;

    /**
     * @var string
     */
    private $suiteUuid;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $title;

    /**
     * @var Description
     */
    private $description;

    /**
     * @var array
     */
    private $labels;

    /**
     * @var array
     */
    private $parameters;

    public function __construct($suiteUuid, $name)
    {
        $this->suiteUuid = $suiteUuid;
        $this->name = $name;
        $this->labels = [];
        $this->parameters = [];
    }

    public function process(Entity $context)
    {
        if ($context instanceof TestCase) {
            $context->setName($this->getName());
            $context->setStatus(Status::PASSED);
            $context->setStart(self::getTimestamp());
            $context->setTitle($this->getTitle());
            $description = $this->getDescription();
            if (isset($description)) {
                $context->setDescription($description);
            }
            foreach ($this->getLabels() as $label) {
                $context->addLabel($label);
            }
            foreach ($this->getParameters() as $parameter) {
                $context->addParameter($parameter);
            }
        }
    }

    /**
     * @param string $title
     * @return $this
     */
    public function withTitle($title)
    {
        $this->setTitle($title);

        return $this;
    }

    /**
     * @param Description $description
     * @return $this
     */
    public function withDescription(Description $description)
    {
        $this->setDescription($description);

        return $this;
    }

    /**
     * @param array $labels
     * @return $this
     */
    public function withLabels(array $labels)
    {
        $this->setLabels($labels);

        return $this;
    }

    /**
     * @param array $parameters
     * @return $this
     */
    public function withParameters(array $parameters)
    {
        $this->setParameters($parameters);

        return $this;
    }

    /**
     * @return string
     */
    public function getSuiteUuid()
    {
        return $this->suiteUuid;
    }

    /**
     * @param \Yandex\Allure\Adapter\Model\Description $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @param array $labels
     */
    public function setLabels(array $labels)
    {
        $this->labels = $labels;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @param array $parameters
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @return \Yandex\Allure\Adapter\Model\Description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return array
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
