<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Model;

use JsonException;
use PHPUnit\Framework\TestCase;
use Qameta\Allure\Model\LinkType;

use function json_encode;

use const JSON_THROW_ON_ERROR;

/**
 * @covers \Qameta\Allure\Model\LinkType
 * @covers \Qameta\Allure\Model\AbstractEnum
 */
class LinkTypeTest extends TestCase
{
    public function testIssue_CalledTwice_ReturnsSameInstance(): void
    {
        $linkType = LinkType::issue();
        self::assertSame($linkType, LinkType::issue());
    }

    public function testIssue_SerializedToJson_ReturnsMatchingValue(): void
    {
        self::assertSame('"issue"', $this->serializeToJson(LinkType::issue()));
    }

    public function testIssue_CastedToString_ReturnsMatchingValue(): void
    {
        self::assertSame('issue', (string) LinkType::issue());
    }

    public function testTms_CalledTwice_ReturnsSameInstance(): void
    {
        $linkType = LinkType::tms();
        self::assertSame($linkType, LinkType::tms());
    }

    public function testTms_SerializedToJson_ReturnsMatchingValue(): void
    {
        self::assertSame('"tms"', $this->serializeToJson(LinkType::tms()));
    }

    public function testTms_CastedToString_ReturnsMatchingValue(): void
    {
        self::assertSame('tms', (string) LinkType::tms());
    }

    public function testCustom_CalledTwice_ReturnsSameInstance(): void
    {
        $linkType = LinkType::custom();
        self::assertSame($linkType, LinkType::custom());
    }

    public function testCustom_SerializedToJson_ReturnsMatchingValue(): void
    {
        self::assertSame('"custom"', $this->serializeToJson(LinkType::custom()));
    }

    public function testCustom_CastedToString_ReturnsMatchingValue(): void
    {
        self::assertSame('custom', (string) LinkType::custom());
    }

    /**
     * @dataProvider providerFromOptionalStringInvalidValue
     */
    public function testFromOptionalString_InvalidValueGiven_ReturnsCustomType(?string $value): void
    {
        self::assertSame(LinkType::custom(), LinkType::fromOptionalString($value));
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

    public function testFromOptionalString_HiddenValueGiven_ReturnsMaskedMode(): void
    {
        self::assertSame(LinkType::issue(), LinkType::fromOptionalString('issue'));
    }

    public function testFromOptionalString_TmsValueGiven_ReturnsTmsType(): void
    {
        self::assertSame(LinkType::tms(), LinkType::fromOptionalString('tms'));
    }

    /**
     * @throws JsonException
     */
    private function serializeToJson(mixed $value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR);
    }
}
