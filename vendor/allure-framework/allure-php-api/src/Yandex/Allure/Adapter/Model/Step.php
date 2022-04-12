<?php

namespace Yandex\Allure\Adapter\Model;

use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlAttribute;
use JMS\Serializer\Annotation\XmlList;
use JMS\Serializer\Annotation\XmlElement;

class Step implements Entity
{

    /**
     * @var int
     * @Type("integer")
     * @XmlAttribute
     */
    private $start;

    /**
     * @var int
     * @Type("integer")
     * @XmlAttribute
     */
    private $stop;

    /**
     * @var string
     * @Type("string")
     * @XmlElement
     */
    private $name;

    /**
     * @var string
     * @Type("string")
     * @XmlElement
     */
    private $title;

    /**
     * @var array
     * @Type("array<Yandex\Allure\Adapter\Model\Step>")
     * @XmlList(entry = "step")
     */
    private $steps;

    /**
     * @var array
     * @Type("array<Yandex\Allure\Adapter\Model\Attachment>")
     * @XmlList(entry = "attachment")
     */
    private $attachments;

    /**
     * @var Status
     * @Type("string")
     * @XmlAttribute
     */
    private $status;

    public function __construct()
    {
        $this->steps = [];
        $this->attachments = [];
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param int $start
     */
    public function setStart($start)
    {
        $this->start = $start;
    }

    /**
     * @param int $stop
     */
    public function setStop($stop)
    {
        $this->stop = $stop;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = ConstantChecker::validate('Yandex\Allure\Adapter\Model\Status', $status);
    }

    /**
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @return array
     */
    public function getSteps()
    {
        return $this->steps;
    }

    /**
     * @return int
     */
    public function getStop()
    {
        return $this->stop;
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
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @param \Yandex\Allure\Adapter\Model\Step $step
     */
    public function addStep(Step $step)
    {
        $this->steps[] = $step;
    }

    /**
     * @param \Yandex\Allure\Adapter\Model\Attachment $attachment
     */
    public function addAttachment(Attachment $attachment)
    {
        $this->attachments[] = $attachment;
    }

    /**
     * @param $index
     */
    public function removeAttachment($index)
    {
        if (isset($this->attachments[$index])) {
            unset($this->attachments[$index]);
        }
    }
}
