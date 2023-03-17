<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Model;

use JsonException;
use PHPUnit\Framework\TestCase;
use Qameta\Allure\Model\Status;

use function json_encode;

use const JSON_THROW_ON_ERROR;

/**
 * @covers \Qameta\Allure\Model\Status
 * @covers \Qameta\Allure\Model\AbstractEnum
 */
class StatusTest extends TestCase
{
    public function testFailed_CalledTwice_ReturnsSameInstance(): void
    {
        $status = Status::failed();
        self::assertSame($status, Status::failed());
    }

    /**
     * @throws JsonException
     */
    public function testFailed_SerializedToJson_ReturnsMatchingJsonValue(): void
    {
        self::assertSame('"failed"', $this->serializeToJson(Status::failed()));
    }

    public function testFailed_CastedToString_ReturnsMatchingValue(): void
    {
        self::assertSame('failed', (string) Status::failed());
    }

    public function testBroken_CalledTwice_ReturnsSameInstance(): void
    {
        $status = Status::broken();
        self::assertSame($status, Status::broken());
    }

    /**
     * @throws JsonException
     */
    public function testBroken_SerializedToJson_ReturnsMatchingJsonValue(): void
    {
        self::assertSame('"broken"', $this->serializeToJson(Status::broken()));
    }

    public function testBroken_CastedToString_ReturnsMatchingValue(): void
    {
        self::assertSame('broken', (string) Status::broken());
    }

    public function testPassed_CalledTwice_ReturnsSameInstance(): void
    {
        $status = Status::passed();
        self::assertSame($status, Status::passed());
    }

    /**
     * @throws JsonException
     */
    public function testPassed_SerializedToJson_ReturnsMatchingJsonValue(): void
    {
        self::assertSame('"passed"', $this->serializeToJson(Status::passed()));
    }

    public function testPassed_CastedToString_ReturnsMatchingValue(): void
    {
        self::assertSame('passed', (string) Status::passed());
    }

    public function testSkipped_CalledTwice_ReturnsSameInstance(): void
    {
        $status = Status::skipped();
        self::assertSame($status, Status::skipped());
    }

    /**
     * @throws JsonException
     */
    public function testSkipped_SerializedToJson_ReturnsMatchingJsonValue(): void
    {
        self::assertSame('"skipped"', $this->serializeToJson(Status::skipped()));
    }

    public function testSkipped_CastedToString_ReturnsMatchingValue(): void
    {
        self::assertSame('skipped', (string) Status::skipped());
    }

    /**
     * @throws JsonException
     */
    private function serializeToJson(mixed $value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR);
    }
}
