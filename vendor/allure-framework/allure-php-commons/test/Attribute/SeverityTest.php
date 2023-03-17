<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Attribute;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Attribute\Severity;

/**
 * @covers \Qameta\Allure\Attribute\Severity
 * @covers \Qameta\Allure\Attribute\AbstractLabel
 */
class SeverityTest extends TestCase
{
    use AnnotationTestTrait;

    public function testGetValue_WithoutValue_ResultHasDefaultValue(): void
    {
        $severity = $this->getSeverityInstance('demoWithoutValue');
        self::assertSame('normal', $severity->getValue());
    }

    public function testGetValue_WithStringValue_ResultHasSameValue(): void
    {
        $severity = $this->getSeverityInstance('demoWithStringValue');
        self::assertSame('a', $severity->getValue());
    }

    public function testGetValue_WithBlockerValue_ResultHasBlockerValue(): void
    {
        $severity = $this->getSeverityInstance('demoWithBlockerValue');
        self::assertSame('blocker', $severity->getValue());
    }

    public function testGetValue_WithCriticalValue_ResultHasCriticalValue(): void
    {
        $severity = $this->getSeverityInstance('demoWithCriticalValue');
        self::assertSame('critical', $severity->getValue());
    }

    public function testGetValue_WithMinorValue_ResultHasMinorValue(): void
    {
        $severity = $this->getSeverityInstance('demoWithMinorValue');
        self::assertSame('minor', $severity->getValue());
    }

    public function testGetValue_WithNormalValue_ResultHasNormalValue(): void
    {
        $severity = $this->getSeverityInstance('demoWithNormalValue');
        self::assertSame('normal', $severity->getValue());
    }

    public function testGetValue_WithTrivalValue_ResultHasTrivialValue(): void
    {
        $severity = $this->getSeverityInstance('demoWithTrivialValue');
        self::assertSame('trivial', $severity->getValue());
    }

    #[Severity]
    protected function demoWithoutValue(): void
    {
    }

    /**
     * @noinspection PhpExpectedValuesShouldBeUsedInspection
     */
    #[Severity("a")]
    protected function demoWithStringValue(): void
    {
    }

    #[Severity(Severity::BLOCKER)]
    protected function demoWithBlockerValue(): void
    {
    }

    #[Severity(Severity::CRITICAL)]
    protected function demoWithCriticalValue(): void
    {
    }

    #[Severity(Severity::MINOR)]
    protected function demoWithMinorValue(): void
    {
    }

    #[Severity(Severity::NORMAL)]
    protected function demoWithNormalValue(): void
    {
    }

    #[Severity(Severity::TRIVIAL)]
    protected function demoWithTrivialValue(): void
    {
    }

    private function getSeverityInstance(string $methodName): Severity
    {
        return $this->getAttributeInstance(Severity::class, $methodName);
    }
}
