<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Model;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Model\ResultType;

/**
 * @covers \Qameta\Allure\Model\ResultType
 * @covers \Qameta\Allure\Model\AbstractEnum
 */
class ResultTypeTest extends TestCase
{
    public function testUnknown_CalledTwice_ReturnsSameInstance(): void
    {
        $resultType = ResultType::unknown();
        self::assertSame($resultType, ResultType::unknown());
    }

    public function testUnknown_CastedToString_ReturnsMatchingValue(): void
    {
        self::assertSame('unknown', (string) ResultType::unknown());
    }

    public function testContainer_CalledTwice_ReturnsSameInstance(): void
    {
        $resultType = ResultType::container();
        self::assertSame($resultType, ResultType::container());
    }

    public function testContainer_CastedToString_ReturnsMatchingValue(): void
    {
        self::assertSame('container', (string) ResultType::container());
    }

    public function testFixture_CalledTwice_ReturnsSameInstance(): void
    {
        $resultType = ResultType::fixture();
        self::assertSame($resultType, ResultType::fixture());
    }

    public function testFixture_CastedToString_ReturnsMatchingValue(): void
    {
        self::assertSame('fixture', (string) ResultType::fixture());
    }

    public function testTest_CalledTwice_ReturnsSameInstance(): void
    {
        $resultType = ResultType::test();
        self::assertSame($resultType, ResultType::test());
    }

    public function testTest_CastedToString_ReturnsMatchingValue(): void
    {
        self::assertSame('test', (string) ResultType::test());
    }

    public function testStep_CalledTwice_ReturnsSameInstance(): void
    {
        $resultType = ResultType::step();
        self::assertSame($resultType, ResultType::step());
    }

    public function testStep_CastedToString_ReturnsMatchingValue(): void
    {
        self::assertSame('step', (string) ResultType::step());
    }

    public function testAttachment_CalledTwice_ReturnsSameInstance(): void
    {
        $resultType = ResultType::step();
        self::assertSame($resultType, ResultType::step());
    }

    public function testAttachment_CastedToString_ReturnsMatchingValue(): void
    {
        self::assertSame('attachment', (string) ResultType::attachment());
    }

    public function testExecutableContext_CalledTwice_ReturnsSameInstance(): void
    {
        $resultType = ResultType::executableContext();
        self::assertSame($resultType, ResultType::executableContext());
    }

    public function testExecutableContext_CastedToString_ReturnsMatchingValue(): void
    {
        self::assertSame('executable_context', (string) ResultType::executableContext());
    }
}
