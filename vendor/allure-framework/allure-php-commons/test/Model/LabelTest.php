<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Model;

use JsonException;
use PHPUnit\Framework\TestCase;
use Qameta\Allure\Model\Label;
use Qameta\Allure\Model\Severity;

use function json_encode;

use const JSON_THROW_ON_ERROR;

/**
 * @covers \Qameta\Allure\Model\Label
 * @covers \Qameta\Allure\Model\JsonSerializableTrait
 */
class LabelTest extends TestCase
{
    public function testId_Always_ResultHasAsIdName(): void
    {
        self::assertSame('AS_ID', Label::id(null)->getName());
    }

    public function testId_GivenNull_ResultHasNullValue(): void
    {
        self::assertNull(Label::id(null)->getValue());
    }

    public function testId_GivenNotNull_ResultHasSameValue(): void
    {
        self::assertSame('a', Label::id('a')->getValue());
    }

    public function testSuite_Always_ResultHasSuiteName(): void
    {
        self::assertSame('suite', Label::suite(null)->getName());
    }

    public function testSuite_GivenNull_ResultHasNullValue(): void
    {
        self::assertNull(Label::suite(null)->getValue());
    }

    public function testSuite_GivenNotNull_ResultHasSameValue(): void
    {
        self::assertSame('a', Label::suite('a')->getValue());
    }

    public function testParentSuite_Always_ResultHasSuiteName(): void
    {
        self::assertSame('parentSuite', Label::parentSuite(null)->getName());
    }

    public function testParentSuite_GivenNull_ResultHasNullValue(): void
    {
        self::assertNull(Label::parentSuite(null)->getValue());
    }

    public function testParentSuite_GivenNotNull_ResultHasSameValue(): void
    {
        self::assertSame('a', Label::parentSuite('a')->getValue());
    }

    public function testSubSuite_Always_ResultHasSuiteName(): void
    {
        self::assertSame('subSuite', Label::subSuite(null)->getName());
    }

    public function testSubSuite_GivenNull_ResultHasNullValue(): void
    {
        self::assertNull(Label::subSuite(null)->getValue());
    }

    public function testSubSuite_GivenNotNull_ResultHasSameValue(): void
    {
        self::assertSame('a', Label::subSuite('a')->getValue());
    }

    public function testEpic_Always_ResultHasSuiteName(): void
    {
        self::assertSame('epic', Label::epic(null)->getName());
    }

    public function testEpic_GivenNull_ResultHasNullValue(): void
    {
        self::assertNull(Label::epic(null)->getValue());
    }

    public function testEpic_GivenNotNull_ResultHasSameValue(): void
    {
        self::assertSame('a', Label::epic('a')->getValue());
    }

    public function testFeature_Always_ResultHasSuiteName(): void
    {
        self::assertSame('feature', Label::feature(null)->getName());
    }

    public function testFeature_GivenNull_ResultHasNullValue(): void
    {
        self::assertNull(Label::feature(null)->getValue());
    }

    public function testFeature_GivenNotNull_ResultHasSameValue(): void
    {
        self::assertSame('a', Label::feature('a')->getValue());
    }

    public function testStory_Always_ResultHasSuiteName(): void
    {
        self::assertSame('story', Label::story(null)->getName());
    }

    public function testStory_GivenNull_ResultHasNullValue(): void
    {
        self::assertNull(Label::story(null)->getValue());
    }

    public function testStory_GivenNotNull_ResultHasSameValue(): void
    {
        self::assertSame('a', Label::story('a')->getValue());
    }

    public function testSeverity_Always_ResultHasSuiteName(): void
    {
        self::assertSame('severity', Label::severity(Severity::trivial())->getName());
    }

    public function testSeverity_GivenNotNull_ResultHasMatchingValue(): void
    {
        $value = Severity::trivial();
        self::assertSame('trivial', Label::severity($value)->getValue());
    }

    public function testTag_Always_ResultHasSuiteName(): void
    {
        self::assertSame('tag', Label::tag(null)->getName());
    }

    public function testTag_GivenNull_ResultHasNullValue(): void
    {
        self::assertNull(Label::tag(null)->getValue());
    }

    public function testTag_GivenNotNull_ResultHasSameValue(): void
    {
        self::assertSame('a', Label::tag('a')->getValue());
    }

    public function testOwner_Always_ResultHasSuiteName(): void
    {
        self::assertSame('owner', Label::owner(null)->getName());
    }

    public function testOwner_GivenNull_ResultHasNullValue(): void
    {
        self::assertNull(Label::owner(null)->getValue());
    }

    public function testOwner_GivenNotNull_ResultHasSameValue(): void
    {
        self::assertSame('a', Label::owner('a')->getValue());
    }

    public function testLead_Always_ResultHasSuiteName(): void
    {
        self::assertSame('lead', Label::lead(null)->getName());
    }

    public function testLead_GivenNull_ResultHasNullValue(): void
    {
        self::assertNull(Label::lead(null)->getValue());
    }

    public function testLead_GivenNotNull_ResultHasSameValue(): void
    {
        self::assertSame('a', Label::lead('a')->getValue());
    }

    public function testHost_Always_ResultHasSuiteName(): void
    {
        self::assertSame('host', Label::host(null)->getName());
    }

    public function testHost_GivenNull_ResultHasNullValue(): void
    {
        self::assertNull(Label::host(null)->getValue());
    }

    public function testHost_GivenNotNull_ResultHasSameValue(): void
    {
        self::assertSame('a', Label::host('a')->getValue());
    }

    public function testThread_Always_ResultHasSuiteName(): void
    {
        self::assertSame('thread', Label::thread(null)->getName());
    }

    public function testThread_GivenNull_ResultHasNullValue(): void
    {
        self::assertNull(Label::thread(null)->getValue());
    }

    public function testThread_GivenNotNull_ResultHasSameValue(): void
    {
        self::assertSame('a', Label::thread('a')->getValue());
    }

    public function testTestMethod_Always_ResultHasSuiteName(): void
    {
        self::assertSame('testMethod', Label::testMethod(null)->getName());
    }

    public function testTestMethod_GivenNull_ResultHasNullValue(): void
    {
        self::assertNull(Label::testMethod(null)->getValue());
    }

    public function testTestMethod_GivenNotNull_ResultHasSameValue(): void
    {
        self::assertSame('a', Label::testMethod('a')->getValue());
    }

    public function testTestClass_Always_ResultHasSuiteName(): void
    {
        self::assertSame('testClass', Label::testClass(null)->getName());
    }

    public function testTestClass_GivenNull_ResultHasNullValue(): void
    {
        self::assertNull(Label::testClass(null)->getValue());
    }

    public function testTestClass_GivenNotNull_ResultHasSameValue(): void
    {
        self::assertSame('a', Label::testClass('a')->getValue());
    }

    public function testPackage_Always_ResultHasSuiteName(): void
    {
        self::assertSame('package', Label::package(null)->getName());
    }

    public function testPackage_GivenNull_ResultHasNullValue(): void
    {
        self::assertNull(Label::package(null)->getValue());
    }

    public function testPackage_GivenNotNull_ResultHasSameValue(): void
    {
        self::assertSame('a', Label::package('a')->getValue());
    }

    public function testFramework_Always_ResultHasSuiteName(): void
    {
        self::assertSame('framework', Label::framework(null)->getName());
    }

    public function testFramework_GivenNull_ResultHasNullValue(): void
    {
        self::assertNull(Label::framework(null)->getValue());
    }

    public function testFramework_GivenNotNull_ResultHasSameValue(): void
    {
        self::assertSame('a', Label::framework('a')->getValue());
    }

    public function testLanguage_Always_ResultHasSuiteName(): void
    {
        self::assertSame('language', Label::language(null)->getName());
    }

    public function testLanguage_GivenNull_ResultIsPhpWithCurrentVersion(): void
    {
        $value = Label::language(null)->getValue();
        self::assertIsString($value);
        self::assertMatchesRegularExpression('#^PHP \d+\.\d+$#', $value);
    }

    public function testLanguage_GivenNotNull_ResultHasSameValue(): void
    {
        self::assertSame('a', Label::language('a')->getValue());
    }

    public function testGetName_ConstructedWithoutName_ReturnsNull(): void
    {
        $label = new Label();
        self::assertNull($label->getName());
    }

    /**
     * @dataProvider providerName
     */
    public function testGetName_ConstructedWithName_ReturnsSameName(?string $value): void
    {
        $label = new Label(name: $value);
        self::assertSame($value, $label->getName());
    }

    /**
     * @return iterable<string, array{string|null}>
     */
    public static function providerName(): iterable
    {
        return [
            'Null' => [null],
            'String' => ['a'],
        ];
    }

    public function testSetName_Always_ReturnsSelf(): void
    {
        $label = new Label();
        self::assertSame($label, $label->setName(null));
    }

    /**
     * @dataProvider providerName
     */
    public function testSetName_GivenName_GetNameReturnsSameName(?string $value): void
    {
        $label = new Label();
        $label->setName($value);
        self::assertSame($value, $label->getName());
    }

    public function testGetValue_ConstructedWithoutValue_ReturnsNull(): void
    {
        $label = new Label();
        self::assertNull($label->getValue());
    }

    /**
     * @dataProvider providerValue
     */
    public function testGetValue_ConstructedWithNameValue_ReturnsSameValue(?string $value): void
    {
        $label = new Label(value: $value);
        self::assertSame($value, $label->getValue());
    }

    /**
     * @return iterable<string, array{string|null}>
     */
    public static function providerValue(): iterable
    {
        return [
            'Null' => [null],
            'String' => ['a'],
        ];
    }

    public function testSetValue_Always_ReturnsSelf(): void
    {
        $label = new Label();
        self::assertSame($label, $label->setValue(null));
    }

    /**
     * @dataProvider providerValue
     */
    public function testSetValue_GivenValue_GetValueReturnsSameValue(?string $value): void
    {
        $label = new Label();
        $label->setValue($value);
        self::assertSame($value, $label->getValue());
    }

    /**
     * @throws JsonException
     * @dataProvider providerJsonSerialize
     */
    public function testJsonSerialize_GivenNameAndValue_ReturnsMatchingValue(
        ?string $name,
        ?string $value,
        string $expectedJson,
    ): void {
        $label = new Label(name: $name, value: $value);
        self::assertJsonStringEqualsJsonString($expectedJson, $this->serializeToJson($label));
    }

    /**
     * @return iterable<string, array{string|null, string|null, string}>
     */
    public static function providerJsonSerialize(): iterable
    {
        return [
            'Null name and value' => [null, null, '{"name":null,"value":null}'],
            'Null name' => ['a', null, '{"name":"a","value":null}'],
            'Null value' => [null, 'a', '{"name":null,"value":"a"}'],
            'Non-null name and value' => ['a', 'b', '{"name":"a","value":"b"}'],
        ];
    }

    /**
     * @throws JsonException
     */
    private function serializeToJson(mixed $value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR);
    }
}
