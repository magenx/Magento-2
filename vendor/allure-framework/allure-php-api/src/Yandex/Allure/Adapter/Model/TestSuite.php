<?php

namespace Yandex\Allure\Adapter\Model;

use JMS\Serializer\Annotation\XmlElement;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlAttribute;
use JMS\Serializer\Annotation\XmlList;
use JMS\Serializer\Annotation\XmlNamespace;
use JMS\Serializer\Annotation\XmlRoot;

/**
 * @package Yandex\Allure\Adapter\Model
 * @XmlNamespace(uri="urn:model.allure.qatools.yandex.ru", prefix="alr")
 * @XmlRoot(name="alr:test-suite")
 * @ExclusionPolicy("none")
 */
class TestSuite implements \Serializable, Entity
{

    const DEFAULT_VERSION = '1.4.0';

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
     * @XmlAttribute
     */
    private $version;

    /**
     * @var string
     * @Type("string")
     * @XmlElement(cdata=false)
     */
    private $name;

    /**
     * @var string
     * @Type("string")
     * @XmlElement(cdata=false)
     */
    private $title;

    /**
     * @var Description
     * @Type("Yandex\Allure\Adapter\Model\Description")
     */
    private $description;

    /**
     * @var array
     * @Type("array<Yandex\Allure\Adapter\Model\TestCase>")
     * @XmlList(entry = "test-case")
     * @SerializedName("test-cases")
     */
    private $testCases;

    /**
     * @var array
     * @Type("array<Yandex\Allure\Adapter\Model\Label>")
     * @XmlList(entry = "label")
     */
    private $labels;

    /**
     * @var Serializer
     * @Exclude
     */
    private $serializer;

    /**
     * @var TestCase
     * @Type("Yandex\Allure\Adapter\Model\TestCase")
     * @Exclude
     */
    private $currentTestCase;

    public function __construct()
    {
        $this->testCases = [];
        $this->labels = [];
        $this->version = self::DEFAULT_VERSION;
    }

    /**
     * @return Description
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
     * @return int
     */
    public function getStop()
    {
        return $this->stop;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return int
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param int $start
     */
    public function setStart($start)
    {
        $this->start = $start;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @param int $stop
     */
    public function setStop($stop)
    {
        $this->stop = $stop;
    }

    /**
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @param \Yandex\Allure\Adapter\Model\Description $description
     */
    public function setDescription(Description $description)
    {
        $this->description = $description;
    }

    /**
     * @param \Yandex\Allure\Adapter\Model\TestCase $testCase
     */
    public function addTestCase(TestCase $testCase)
    {
        $this->testCases[$testCase->getName()] = $testCase;
    }

    /**
     * Returns test case by name
     * @param string $name
     * @return \Yandex\Allure\Adapter\Model\TestCase
     */
    public function getTestCase($name)
    {
        return $this->testCases[$name];
    }

    /**
     * Return total count of child elements (test cases or test suites)
     * @return int
     */
    public function size()
    {
        return count($this->testCases);
    }

    /**
     * @param \Yandex\Allure\Adapter\Model\Label $label
     */
    public function addLabel(Label $label)
    {
        $this->labels[] = $label;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return $this->getSerializer()->serialize($this, 'xml');
    }

    /**
     * @param string $serialized
     * @return mixed
     */
    public function unserialize($serialized)
    {
        return $this->getSerializer()->deserialize($serialized, \Yandex\Allure\Adapter\Model\TestSuite::class, 'xml');
    }
    
    /**
    +     * @return string
    +     */
    public function __serialize()
    {
        return $this->getSerializer()->serialize($this, 'xml');
    }
    
    /**
    +     * @param string $serialized
    +     * @return mixed
    +     */
    public function __unserialize($serialized)
    {
        return $this->getSerializer()->deserialize($serialized, \Yandex\Allure\Adapter\Model\TestSuite::class, 'xml');
    }

    /**
     * @return Serializer
     */
    private function getSerializer()
    {
        if (!isset($this->serializer)) {
            $this->serializer = SerializerBuilder::create()->build();
        }

        return $this->serializer;
    }
}
