<?php

namespace Yandex\Allure\Adapter\Model;

use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlAttribute;

/**
 * @package Yandex\Allure\Adapter\Model
 */
class Attachment implements Entity
{

    /**
     * @var string
     * @Type("string")
     * @XmlAttribute
     */
    private $title;

    /**
     * @var string
     * @Type("string")
     * @XmlAttribute
     */
    private $source;

    /**
     * @var string
     * @Type("string")
     * @XmlAttribute
     */
    private $type;

    public function __construct($title, $source, $type)
    {
        $this->source = $source;
        $this->title = $title;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
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
    public function getType()
    {
        return $this->type;
    }
}
