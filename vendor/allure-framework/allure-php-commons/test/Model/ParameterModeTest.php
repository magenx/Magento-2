<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Model;

use JsonException;
use PHPUnit\Framework\TestCase;
use Qameta\Allure\Model\ParameterMode;

use function json_encode;

use const JSON_THROW_ON_ERROR;

/**
 * @covers \Qameta\Allure\Model\ParameterMode
 * @covers \Qameta\Allure\Model\AbstractEnum
 */
class ParameterModeTest extends TestCase
{
    public function testMasked_CalledTwice_ReturnsSameInstance(): void
    {
        $mode = ParameterMode::masked();
        self::assertSame($mode, ParameterMode::masked());
    }

    public function testMasked_SerializedToJson_ReturnsMatchingValue(): void
    {
        self::assertSame('"masked"', $this->serializeToJson(ParameterMode::masked()));
    }

    public function testMasked_CastedToString_ReturnsMatchingValue(): void
    {
        self::assertSame('masked', (string) ParameterMode::masked());
    }

    public function testHidden_CalledTwice_ReturnsSameInstance(): void
    {
        $mode = ParameterMode::hidden();
        self::assertSame($mode, ParameterMode::hidden());
    }

    public function testHidden_SerializedToJson_ReturnsMatchingValue(): void
    {
        self::assertSame('"hidden"', $this->serializeToJson(ParameterMode::hidden()));
    }

    public function testHidden_CastedToString_ReturnsMatchingValue(): void
    {
        self::assertSame('hidden', (string) ParameterMode::hidden());
    }

    /**
     * @dataProvider providerFromOptionalStringInvalidValue
     */
    public function testFromOptionalString_InvalidValueGiven_ReturnsNull(?string $value): void
    {
        self::assertNull(ParameterMode::fromOptionalString($value));
    }

    /**
     * @return iterable<string, array{string|null}>
     */
    public static function providerFromOptionalStringInvalidValue(): iterable
    {
        return [
            'Null' => [null],
            'Invalid string' => ['a'],
        ];
    }

    public function testFromOptionalString_MaskedValueGiven_ReturnsMaskedMode(): void
    {
        self::assertSame(ParameterMode::masked(), ParameterMode::fromOptionalString('masked'));
    }

    public function testFromOptionalString_HiddenValueGiven_ReturnsMaskedMode(): void
    {
        self::assertSame(ParameterMode::hidden(), ParameterMode::fromOptionalString('hidden'));
    }

    /**
     * @throws JsonException
     */
    private function serializeToJson(mixed $value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR);
    }
}
