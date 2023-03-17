<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Model;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Model\StatusDetails;

/**
 * @covers \Qameta\Allure\Model\StatusDetails
 */
class StatusDetailsTest extends TestCase
{
    public function testIsKnown_ConstructedWithoutKnownValue_ReturnsNull(): void
    {
        $statusDetails = new StatusDetails();
        self::assertNull($statusDetails->isKnown());
    }

    /**
     * @dataProvider providerIsKnownValue
     */
    public function testIsKnown_ConstructedWithKnownValue_ReturnsSameValue(?bool $value): void
    {
        $statusDetails = new StatusDetails(known: $value);
        self::assertSame($value, $statusDetails->isKnown());
    }

    /**
     * @return iterable<string, array{bool|null}>
     */
    public static function providerIsKnownValue(): iterable
    {
        return [
            'Null' => [null],
            'True' => [true],
            'False' => [false],
        ];
    }

    public function testMakeKnown_Always_ReturnsSelf(): void
    {
        $statusDetails = new StatusDetails();
        self::assertSame($statusDetails, $statusDetails->makeKnown(null));
    }

    /**
     * @dataProvider providerIsKnownValue
     */
    public function testMakeKnown_GivenKnownValue_IsKnownReturnsSameValue(?bool $value): void
    {
        $statusDetails = new StatusDetails();
        $statusDetails->makeKnown($value);
        self::assertSame($value, $statusDetails->isKnown());
    }

    public function testIsMuted_ConstructedWithoutMutedValue_ReturnsNull(): void
    {
        $statusDetails = new StatusDetails();
        self::assertNull($statusDetails->isMuted());
    }

    /**
     * @dataProvider providerIsMutedValue
     */
    public function testIsMuted_ConstructedWithMutedValue_ReturnsSameValue(?bool $value): void
    {
        $statusDetails = new StatusDetails(muted: $value);
        self::assertSame($value, $statusDetails->isMuted());
    }

    /**
     * @return iterable<string, array{bool|null}>
     */
    public static function providerIsMutedValue(): iterable
    {
        return [
            'Null' => [null],
            'True' => [true],
            'False' => [false],
        ];
    }

    public function testMakeMuted_Always_ReturnsSelf(): void
    {
        $statusDetails = new StatusDetails();
        self::assertSame($statusDetails, $statusDetails->makeMuted(null));
    }

    /**
     * @dataProvider providerIsMutedValue
     */
    public function testMakeMuted_GivenMutedValue_IsMutedReturnsSameValue(?bool $value): void
    {
        $statusDetails = new StatusDetails();
        $statusDetails->makeMuted($value);
        self::assertSame($value, $statusDetails->isMuted());
    }

    public function testIsFlaky_ConstructedWithoutFlakyValue_ReturnsNull(): void
    {
        $statusDetails = new StatusDetails();
        self::assertNull($statusDetails->isFlaky());
    }

    /**
     * @dataProvider providerIsFlakyValue
     */
    public function testIsFlaky_ConstructedWithFlakyValue_ReturnsSameValue(?bool $value): void
    {
        $statusDetails = new StatusDetails(flaky: $value);
        self::assertSame($value, $statusDetails->isFlaky());
    }

    /**
     * @return iterable<string, array{bool|null}>
     */
    public static function providerIsFlakyValue(): iterable
    {
        return [
            'Null' => [null],
            'True' => [true],
            'False' => [false],
        ];
    }

    public function testMakeFlaky_Always_ReturnsSelf(): void
    {
        $statusDetails = new StatusDetails();
        self::assertSame($statusDetails, $statusDetails->makeFlaky(null));
    }

    /**
     * @dataProvider providerIsFlakyValue
     */
    public function testMakeFlaky_GivenFlakyValue_IsFlakyReturnsSameValue(?bool $value): void
    {
        $statusDetails = new StatusDetails();
        $statusDetails->makeFlaky($value);
        self::assertSame($value, $statusDetails->isFlaky());
    }

    public function testGetMessage_ConstructedWithoutMessageValue_ReturnsNull(): void
    {
        $statusDetails = new StatusDetails();
        self::assertNull($statusDetails->getMessage());
    }

    /**
     * @dataProvider providerGetMessageValue
     */
    public function testGetMessage_ConstructedWithMessageValue_ReturnsSameValue(?string $value): void
    {
        $statusDetails = new StatusDetails(message: $value);
        self::assertSame($value, $statusDetails->getMessage());
    }

    /**
     * @return iterable<string, array{string|null}>
     */
    public static function providerGetMessageValue(): iterable
    {
        return [
            'Null' => [null],
            'String' => ['a'],
        ];
    }

    public function testSetMessage_Always_ReturnsSelf(): void
    {
        $statusDetails = new StatusDetails();
        self::assertSame($statusDetails, $statusDetails->setMessage(null));
    }

    /**
     * @dataProvider providerGetMessageValue
     */
    public function testSetMessage_GivenValue_GetMessageReturnsSameValue(?string $value): void
    {
        $statusDetails = new StatusDetails();
        $statusDetails->setMessage($value);
        self::assertSame($value, $statusDetails->getMessage());
    }

    public function testGetTrace_ConstructedWithoutMessageValue_ReturnsNull(): void
    {
        $statusDetails = new StatusDetails();
        self::assertNull($statusDetails->getTrace());
    }

    /**
     * @dataProvider providerGetTraceValue
     */
    public function testGetTrace_ConstructedWithTraceValue_ReturnsSameValue(?string $value): void
    {
        $statusDetails = new StatusDetails(trace: $value);
        self::assertSame($value, $statusDetails->getTrace());
    }

    /**
     * @return iterable<string, array{string|null}>
     */
    public static function providerGetTraceValue(): iterable
    {
        return [
            'Null' => [null],
            'String' => ['a'],
        ];
    }

    public function testSetTrace_Always_ReturnsSelf(): void
    {
        $statusDetails = new StatusDetails();
        self::assertSame($statusDetails, $statusDetails->setTrace(null));
    }

    /**
     * @dataProvider providerGetTraceValue
     */
    public function testSetTrace_GivenValue_GetTraceReturnsSameValue(?string $value): void
    {
        $statusDetails = new StatusDetails();
        $statusDetails->setTrace($value);
        self::assertSame($value, $statusDetails->getTrace());
    }
}
