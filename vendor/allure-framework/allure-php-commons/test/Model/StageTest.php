<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Model;

use JsonException;
use PHPUnit\Framework\TestCase;
use Qameta\Allure\Model\Stage;

use function json_encode;

use const JSON_THROW_ON_ERROR;

/**
 * @covers \Qameta\Allure\Model\Stage
 * @covers \Qameta\Allure\Model\AbstractEnum
 */
class StageTest extends TestCase
{
    public function testScheduled_CalledTwice_ReturnsSameInstance(): void
    {
        $severity = Stage::scheduled();
        self::assertSame($severity, Stage::scheduled());
    }

    public function testScheduled_SerializedToJson_ReturnsMatchingValue(): void
    {
        self::assertSame('"scheduled"', $this->serializeToJson(Stage::scheduled()));
    }

    public function testScheduled_CastedToString_ReturnsMatchingValue(): void
    {
        self::assertSame('scheduled', (string) Stage::scheduled());
    }

    public function testRunning_CalledTwice_ReturnsSameInstance(): void
    {
        $severity = Stage::running();
        self::assertSame($severity, Stage::running());
    }

    public function testRunning_SerializedToJson_ReturnsMatchingValue(): void
    {
        self::assertSame('"running"', $this->serializeToJson(Stage::running()));
    }

    public function testRunning_CastedToString_ReturnsMatchingValue(): void
    {
        self::assertSame('running', (string) Stage::running());
    }

    public function testFinished_CalledTwice_ReturnsSameInstance(): void
    {
        $severity = Stage::finished();
        self::assertSame($severity, Stage::finished());
    }

    public function testFinished_SerializedToJson_ReturnsMatchingValue(): void
    {
        self::assertSame('"finished"', $this->serializeToJson(Stage::finished()));
    }

    public function testFinished_CastedToString_ReturnsMatchingValue(): void
    {
        self::assertSame('finished', (string) Stage::finished());
    }

    public function testPending_CalledTwice_ReturnsSameInstance(): void
    {
        $severity = Stage::pending();
        self::assertSame($severity, Stage::pending());
    }

    public function testPending_SerializedToJson_ReturnsMatchingValue(): void
    {
        self::assertSame('"pending"', $this->serializeToJson(Stage::pending()));
    }

    public function testPending_CastedToString_ReturnsMatchingValue(): void
    {
        self::assertSame('pending', (string) Stage::pending());
    }

    public function testInterrupted_CalledTwice_ReturnsSameInstance(): void
    {
        $severity = Stage::interrupted();
        self::assertSame($severity, Stage::interrupted());
    }

    public function testInterrupted_SerializedToJson_ReturnsMatchingValue(): void
    {
        self::assertSame('"interrupted"', $this->serializeToJson(Stage::interrupted()));
    }

    public function testInterrupted_CastedToString_ReturnsMatchingValue(): void
    {
        self::assertSame('interrupted', (string) Stage::interrupted());
    }

    /**
     * @throws JsonException
     */
    private function serializeToJson(mixed $value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR);
    }
}
