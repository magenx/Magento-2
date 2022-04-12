<?php

namespace Yandex\Allure\Adapter;

use Doctrine\Common\Annotations\AnnotationRegistry;
use DOMDocument;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\Exporter\Exception;
use Yandex\Allure\Adapter\Event\AddAttachmentEvent;
use Yandex\Allure\Adapter\Event\StepFinishedEvent;
use Yandex\Allure\Adapter\Event\StepStartedEvent;
use Yandex\Allure\Adapter\Event\TestCaseFailedEvent;
use Yandex\Allure\Adapter\Event\TestCaseFinishedEvent;
use Yandex\Allure\Adapter\Event\TestCaseStartedEvent;
use Yandex\Allure\Adapter\Event\TestSuiteFinishedEvent;
use Yandex\Allure\Adapter\Event\TestSuiteStartedEvent;
use Yandex\Allure\Adapter\Model\Description;
use Yandex\Allure\Adapter\Model\DescriptionType;
use Yandex\Allure\Adapter\Model\Label;
use Yandex\Allure\Adapter\Model\Parameter;
use Yandex\Allure\Adapter\Model\ParameterKind;
use Yandex\Allure\Adapter\Model\SeverityLevel;

const TEST_CASE_NAME = 'test-case-name';
const TEST_CASE_TITLE = 'test-case-title';
const TEST_SUITE_UUID = 'test-suite-uuid';
const TEST_SUITE_NAME = 'test-suite-name';
const TEST_SUITE_TITLE = 'test-suite-title';
const DESCRIPTION = 'test-suite-description';
const FEATURE_NAME = 'test-feature';
const STORY_NAME = 'test-story';
const PARAMETER_NAME = 'test-parameter-name';
const PARAMETER_VALUE = 'test-parameter-value';
const FAILURE_MESSAGE = 'failure-message';
const STEP_NAME = 'test-step-name';
const STEP_TITLE = 'test-step-title';
const STEP_ATTACHMENT_TITLE = 'step-attachment-caption';
const STEP_ATTACHMENT_SOURCE = 'step-attachment-source';

class XMLValidationTest extends TestCase
{
    public function testGeneratedXMLIsValid()
    {
        $tmpDir = $this->prepareDirForXML();
        $uuid = $this->generateXML($tmpDir);

        $fileName = $tmpDir . DIRECTORY_SEPARATOR . $uuid . '-testsuite.xml';
        $this->assertTrue(file_exists($fileName));
        $this->validateFileXML($fileName);
    }

    private function prepareDirForXML()
    {
        $tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('allure-xml-test');
        mkdir($tmpDir);
        $this->assertTrue(
            file_exists($tmpDir) &&
            is_writable($tmpDir)
        );

        return $tmpDir;
    }

    private function generateXML($tmpDir)
    {
        AnnotationRegistry::registerAutoloadNamespace(
            'JMS\Serializer\Annotation',
            __DIR__ . "/../../../../vendor/jms/serializer/src"
        );
        Model\Provider::setOutputDirectory($tmpDir);
        Allure::setDefaultLifecycle();
        $testSuiteStartedEvent = new TestSuiteStartedEvent(TEST_SUITE_NAME);
        $uuid = $testSuiteStartedEvent->getUuid();
        $testSuiteStartedEvent->setTitle(TEST_SUITE_TITLE);
        $testSuiteStartedEvent->setDescription(new Description(DescriptionType::HTML, DESCRIPTION));
        $testSuiteStartedEvent->setLabels([
            Label::feature(FEATURE_NAME),
            Label::story(STORY_NAME)
        ]);
        Allure::lifecycle()->fire($testSuiteStartedEvent);

        $testCaseStartedEvent = new TestCaseStartedEvent($uuid, TEST_CASE_NAME);
        $testCaseStartedEvent->setDescription(new Description(DescriptionType::MARKDOWN, DESCRIPTION));
        $testCaseStartedEvent->setLabels([
            Label::feature(FEATURE_NAME),
            Label::story(STORY_NAME),
            Label::severity(SeverityLevel::MINOR)
        ]);
        $testCaseStartedEvent->setTitle(TEST_CASE_TITLE);
        $testCaseStartedEvent->setParameters([
            new Parameter(PARAMETER_NAME, PARAMETER_VALUE, ParameterKind::SYSTEM_PROPERTY)
        ]);
        Allure::lifecycle()->fire($testCaseStartedEvent);

        $testCaseFailureEvent = new TestCaseFailedEvent();
        $testCaseFailureEvent = $testCaseFailureEvent->withMessage(FAILURE_MESSAGE)->withException(new \Exception());
        Allure::lifecycle()->fire($testCaseFailureEvent);

        $stepStartedEvent = new StepStartedEvent(STEP_NAME);
        $stepStartedEvent = $stepStartedEvent->withTitle(STEP_TITLE);
        Allure::lifecycle()->fire($stepStartedEvent);
        Allure::lifecycle()->fire(
            new AddAttachmentEvent(STEP_ATTACHMENT_SOURCE, STEP_ATTACHMENT_TITLE, 'text/plain')
        );
        Allure::lifecycle()->fire(new StepFinishedEvent());

        Allure::lifecycle()->fire(new TestCaseFinishedEvent());
        Allure::lifecycle()->fire(new TestSuiteFinishedEvent($uuid));

        return $uuid;
    }

    private function validateFileXML($fileName)
    {
        libxml_use_internal_errors(true); //Comment this line to see DOMDocument XML validation errors
        $dom = new DOMDocument();
        $dom->load($fileName);
        $schemaFilename = __DIR__ . DIRECTORY_SEPARATOR . 'allure-1.4.0.xsd';
        $isSchemaValid = $dom->schemaValidate($schemaFilename);
        $this->assertTrue($isSchemaValid, 'XML file should be valid');
    }
}
