<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Legacy\Annotation;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Test\Attribute\AnnotationTestTrait;
use Qameta\Allure\Attribute\Severity as QametaSeverity;
use Qameta\Allure\Model;
use Yandex\Allure\Adapter\Annotation\Severity;
use Yandex\Allure\Adapter\Model\SeverityLevel;

/**
 * @covers \Yandex\Allure\Adapter\Annotation\Severity
 */
class SeverityTest extends TestCase
{
    use AnnotationTestTrait;

    public function testLevel_WithoutValue_ReturnsSameStringAsNormalModel(): void
    {
        $severity = $this->getSeverityInstance('demoWithoutValue');
        self::assertSame((string) Model\Severity::normal(), $severity->level);
    }

    public function testLevel_WithStringValue_ReturnsSameValue(): void
    {
        $severity = $this->getSeverityInstance('demoWithStringValue');
        self::assertSame('a', $severity->level);
    }

    public function testLevel_WithBlockerValue_ReturnsSameStringBlockerModel(): void
    {
        $severity = $this->getSeverityInstance('demoWithBlockerValue');
        self::assertSame((string) Model\Severity::blocker(), $severity->level);
    }

    public function testLevel_WithCriticalValue_ReturnsSameStringCriticalModel(): void
    {
        $severity = $this->getSeverityInstance('demoWithCriticalValue');
        self::assertSame((string) Model\Severity::critical(), $severity->level);
    }

    public function testLevel_WithMinorValue_ReturnsSameStringMinorModel(): void
    {
        $severity = $this->getSeverityInstance('demoWithMinorValue');
        self::assertSame((string) Model\Severity::minor(), $severity->level);
    }

    public function testLevel_WithNormalValue_ReturnsSameStringNormalModel(): void
    {
        $severity = $this->getSeverityInstance('demoWithNormalValue');
        self::assertSame((string) Model\Severity::normal(), $severity->level);
    }

    public function testLevel_WithTrivialValue_ReturnsSameStringTrivialModel(): void
    {
        $severity = $this->getSeverityInstance('demoWithTrivialValue');
        self::assertSame((string) Model\Severity::trivial(), $severity->level);
    }

    public function testConvert_WithoutValue_ResultCastsToSameStringAsNormalModel(): void
    {
        $severity = $this->getSeverityInstance('demoWithoutValue');
        $expectedData = [
            'class' => QametaSeverity::class,
            'name' => 'severity',
            'value' => 'normal',
        ];
        self::assertSame($expectedData, $this->exportLabel($severity->convert()));
    }

    public function testConvert_WithStringValue_ResultCastsToSameString(): void
    {
        $severity = $this->getSeverityInstance('demoWithStringValue');
        $expectedData = [
            'class' => QametaSeverity::class,
            'name' => 'severity',
            'value' => 'a',
        ];
        self::assertSame($expectedData, $this->exportLabel($severity->convert()));
    }

    public function testLevel_WithBlockerValue_ResultCastsToSameStringAsBlockerModel(): void
    {
        $severity = $this->getSeverityInstance('demoWithBlockerValue');
        $expectedData = [
            'class' => QametaSeverity::class,
            'name' => 'severity',
            'value' => 'blocker',
        ];
        self::assertSame($expectedData, $this->exportLabel($severity->convert()));
    }

    public function testLevel_WithCriticalValue_ResultCastsToSameStringAsCriticalModel(): void
    {
        $severity = $this->getSeverityInstance('demoWithCriticalValue');
        $expectedData = [
            'class' => QametaSeverity::class,
            'name' => 'severity',
            'value' => 'critical',
        ];
        self::assertSame($expectedData, $this->exportLabel($severity->convert()));
    }

    public function testLevel_WithMinorValue_ResultCastsToSameStringAsMinorModel(): void
    {
        $severity = $this->getSeverityInstance('demoWithMinorValue');
        $expectedData = [
            'class' => QametaSeverity::class,
            'name' => 'severity',
            'value' => 'minor',
        ];
        self::assertSame($expectedData, $this->exportLabel($severity->convert()));
    }

    public function testLevel_WithNormalValue_ResultCastsToSameStringAsNormalModel(): void
    {
        $severity = $this->getSeverityInstance('demoWithNormalValue');
        $expectedData = [
            'class' => QametaSeverity::class,
            'name' => 'severity',
            'value' => 'normal',
        ];
        self::assertSame($expectedData, $this->exportLabel($severity->convert()));
    }

    public function testLevel_WithTrivialValue_ResultCastsToSameStringAsTrivialModel(): void
    {
        $severity = $this->getSeverityInstance('demoWithTrivialValue');
        $expectedData = [
            'class' => QametaSeverity::class,
            'name' => 'severity',
            'value' => 'trivial',
        ];
        self::assertSame($expectedData, $this->exportLabel($severity->convert()));
    }

    /**
     * @Severity
     */
    protected function demoWithoutValue(): void
    {
    }

    /**
     * @Severity("a")
     */
    protected function demoWithStringValue(): void
    {
    }

    /**
     * @Severity(SeverityLevel::BLOCKER)
     */
    protected function demoWithBlockerValue(): void
    {
    }

    /**
     * @Severity(SeverityLevel::CRITICAL)
     */
    protected function demoWithCriticalValue(): void
    {
    }

    /**
     * @Severity(SeverityLevel::MINOR)
     */
    protected function demoWithMinorValue(): void
    {
    }

    /**
     * @Severity(SeverityLevel::NORMAL)
     */
    protected function demoWithNormalValue(): void
    {
    }

    /**
     * @Severity(SeverityLevel::TRIVIAL)
     */
    protected function demoWithTrivialValue(): void
    {
    }

    /**
     * @psalm-suppress DeprecatedClass
     */
    private function getSeverityInstance(string $methodName): Severity
    {
        return $this->getLegacyAttributeInstance(Severity::class, $methodName);
    }
}
