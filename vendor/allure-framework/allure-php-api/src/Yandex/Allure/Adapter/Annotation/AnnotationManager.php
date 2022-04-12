<?php

namespace Yandex\Allure\Adapter\Annotation;

use Yandex\Allure\Adapter\Event\TestCaseStartedEvent;
use Yandex\Allure\Adapter\Event\TestSuiteStartedEvent;
use Yandex\Allure\Adapter\Model;
use Yandex\Allure\Adapter\Model\ConstantChecker;

class AnnotationManager
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var Model\Description
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

    public function __construct(array $annotations)
    {
        $this->labels = [];
        $this->parameters = [];
        $this->processAnnotations($annotations);
    }

    private function processAnnotations(array $annotations)
    {
        foreach ($annotations as $annotation) {
            if ($annotation instanceof AllureId) {
                $this->labels[] = Model\Label::id($annotation->value);
            } elseif ($annotation instanceof Title) {
                $this->title = $annotation->value;
            } elseif ($annotation instanceof Description) {
                $this->description = new Model\Description(
                    $annotation->type,
                    $annotation->value
                );
            } elseif ($annotation instanceof Epics) {
                foreach ($annotation->getEpicNames() as $epicName) {
                    $this->labels[] = Model\Label::epic($epicName);
                }
            } elseif ($annotation instanceof Features) {
                foreach ($annotation->getFeatureNames() as $featureName) {
                    $this->labels[] = Model\Label::feature($featureName);
                }
            } elseif ($annotation instanceof Stories) {
                foreach ($annotation->getStories() as $issueKey) {
                    $this->labels[] = Model\Label::story($issueKey);
                }
            } elseif ($annotation instanceof Issues) {
                foreach ($annotation->getIssueKeys() as $issueKey) {
                    $this->labels[] = Model\Label::issue($issueKey);
                }
            } elseif ($annotation instanceof TestCaseId) {
                foreach ($annotation->getTestCaseIds() as $testCaseId) {
                    $this->labels[] = Model\Label::testId($testCaseId);
                }
            } elseif ($annotation instanceof Severity) {
                $this->labels[] = Model\Label::severity(
                    ConstantChecker::validate('Yandex\Allure\Adapter\Model\SeverityLevel', $annotation->level)
                );
            } elseif ($annotation instanceof TestType) {
                $this->labels[] = Model\Label::testType($annotation->type);
            } elseif ($annotation instanceof Parameter) {
                $this->parameters[] = new Model\Parameter(
                    $annotation->name,
                    $annotation->value,
                    $annotation->kind
                );
            } elseif ($annotation instanceof Parameters) {
                foreach ($annotation->parameters as $parameter) {
                    $this->parameters[] = new Model\Parameter(
                        $parameter->name,
                        $parameter->value,
                        $parameter->kind
                    );
                }
            } elseif ($annotation instanceof Label) {
                foreach ($annotation -> values as $value) {
                    $this->labels[] = Model\Label::label($annotation->name, $value);
                }
            } elseif ($annotation instanceof Labels) {
                foreach ($annotation -> labels as $label) {
                    foreach ($label -> values as $value) {
                        $this->labels[] = Model\Label::label($label->name, $value);
                    }
                }
            }
        }
    }

    public function updateTestSuiteEvent(TestSuiteStartedEvent $event)
    {

        if ($this->isTitlePresent()) {
            $event->setTitle($this->getTitle());
        }

        if ($this->isDescriptionPresent()) {
            $event->setDescription($this->getDescription());
        }

        if ($this->areLabelsPresent()) {
            $event->setLabels($this->getLabels());
        }

    }

    public function updateTestCaseEvent(TestCaseStartedEvent $event)
    {
        if ($this->isTitlePresent()) {
            $event->setTitle($this->getTitle());
        }

        if ($this->isDescriptionPresent()) {
            $event->setDescription($this->getDescription());
        }

        if ($this->areLabelsPresent()) {
            $event->setLabels(array_merge($event->getLabels(), $this->getLabels()));
        }

        if ($this->areParametersPresent()) {
            $event->setParameters($this->getParameters());
        }

    }

    /**
     * @return Model\Description
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

    public function isTitlePresent()
    {
        return isset($this->title);
    }

    public function isDescriptionPresent()
    {
        return isset($this->description);
    }

    public function areLabelsPresent()
    {
        return !empty($this->labels);
    }

    public function areParametersPresent()
    {
        return !empty($this->parameters);
    }
}
