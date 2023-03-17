<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Model;

use JsonException;
use PHPUnit\Framework\TestCase;
use Qameta\Allure\Model\Exception\InvalidSeverityException;
use Qameta\Allure\Model\Severity;

use function json_encode;

use const JSON_THROW_ON_ERROR;

/**
 * @covers \Qameta\Allure\Model\Severity
 * @covers \Qameta\Allure\Model\AbstractEnum
 */
class SeverityTest extends TestCase
{
    public function testFromString_InvalidValue_ThrowsException(): void
    {
        $this->expectException(InvalidSeverityException::class);
        Severity::fromString('a');
    }

    /**
     * @dataProvider providerValues
     */
    public function testFromString_CalledTwice_ReturnsSameInstance(string $value): void
    {
        $severity = Severity::fromString($value);
        self::assertSame($severity, Severity::fromString($value));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function providerValues(): iterable
    {
        return [
            'Blocker' => ['blocker'],
            'Critical' => ['critical'],
        ];
    }

    /**
     * @dataProvider providerValues
     */
    public function testFromString_GivenValue_ResultCastsToSameValue(string $value): void
    {
        $severity = Severity::fromString($value);
        self::assertSame($value, (string) $severity);
    }

    public function testBlocker_CalledTwice_ReturnsSameInstance(): void
    {
        $severity = Severity::blocker();
        self::assertSame($severity, Severity::blocker());
    }

    public function testBlocker_SerializedToJson_ReturnsMatchingValue(): void
    {
        self::assertSame('"blocker"', $this->serializeToJson(Severity::blocker()));
    }

    public function testBlocker_CastedToString_ReturnsMatchingValue(): void
    {
        self::assertSame('blocker', (string) Severity::blocker());
    }

    public function testCritical_CalledTwice_ReturnsSameInstance(): void
    {
        $severity = Severity::critical();
        self::assertSame($severity, Severity::critical());
    }

    public function testCritical_SerializedToJson_ReturnsMatchingValue(): void
    {
        self::assertSame('"critical"', $this->serializeToJson(Severity::critical()));
    }

    public function testCritical_CastedToString_ReturnsMatchingValue(): void
    {
        self::assertSame('critical', (string) Severity::critical());
    }

    public function testNormal_CalledTwice_ReturnsSameInstance(): void
    {
        $severity = Severity::normal();
        self::assertSame($severity, Severity::normal());
    }

    public function testNormal_SerializedToJson_ReturnsMatchingValue(): void
    {
        self::assertSame('"normal"', $this->serializeToJson(Severity::normal()));
    }

    public function testNormal_CastedToString_ReturnsMatchingValue(): void
    {
        self::assertSame('normal', (string) Severity::normal());
    }

    public function testMinor_CalledTwice_ReturnsSameInstance(): void
    {
        $severity = Severity::minor();
        self::assertSame($severity, Severity::minor());
    }

    public function testMinor_SerializedToJson_ReturnsMatchingValue(): void
    {
        self::assertSame('"minor"', $this->serializeToJson(Severity::minor()));
    }

    public function testMinor_CastedToString_ReturnsMatchingValue(): void
    {
        self::assertSame('minor', (string) Severity::minor());
    }

    public function testTrivial_CalledTwice_ReturnsSameInstance(): void
    {
        $severity = Severity::trivial();
        self::assertSame($severity, Severity::trivial());
    }

    public function testTrivial_SerializedToJson_ReturnsMatchingValue(): void
    {
        self::assertSame('"trivial"', $this->serializeToJson(Severity::trivial()));
    }

    public function testTrivial_CastedToString_ReturnsMatchingValue(): void
    {
        self::assertSame('trivial', (string) Severity::trivial());
    }

    /**
     * @throws JsonException
     */
    private function serializeToJson(mixed $value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR);
    }
}
