<?php

namespace Yandex\Allure\Adapter\Annotation\Fixtures;

use Yandex\Allure\Adapter\Annotation\AllureId;
use Yandex\Allure\Adapter\Annotation\Epics;
use Yandex\Allure\Adapter\Annotation\Issues;
use Yandex\Allure\Adapter\Annotation\Title;
use Yandex\Allure\Adapter\Annotation\Description;
use Yandex\Allure\Adapter\Annotation\Features;
use Yandex\Allure\Adapter\Annotation\Stories;
use Yandex\Allure\Adapter\Annotation\Severity;
use Yandex\Allure\Adapter\Annotation\Label;
use Yandex\Allure\Adapter\Annotation\Labels;
use Yandex\Allure\Adapter\Annotation\Parameter;
use Yandex\Allure\Adapter\Model\DescriptionType;
use Yandex\Allure\Adapter\Model\SeverityLevel;
use Yandex\Allure\Adapter\Model\ParameterKind;

/**
 * @Title("test-suite-title")
 * @Description(value="test-suite-description", type=DescriptionType::MARKDOWN)
 * @Epics({"test-suite-epic1", "test-suite-epic2"})
 * @Features({"test-suite-feature1", "test-suite-feature2"})
 * @Stories({"test-suite-story1", "test-suite-story2"})
 * @Issues({"test-suite-issue1", "test-suite-issue2"})
 */
class ExampleTestSuite
{
    /**
     * @AllureId("123")
     * @Title("test-case-title")
     * @Description(value="test-case-description", type=DescriptionType::HTML)
     * @Epics({"test-case-epic1", "test-case-epic2"})
     * @Features({"test-case-feature1", "test-case-feature2"})
     * @Stories({"test-case-story1", "test-case-story2"})
     * @Severity(SeverityLevel::BLOCKER)
     * @Parameter(name = "test-case-param-name", value = "test-case-param-value", kind = ParameterKind::ARGUMENT)
     * @Labels({
     *     @Label(name = "custom-name", values = "custom-value-1"),
     *     @Label(name = "custom-name", values = {"custom-value-2", "custom-value-3"})
     * })
     * @Issues({"test-case-issue1", "test-case-issue2"})
     */
    public function exampleTestCase()
    {
    }
}
