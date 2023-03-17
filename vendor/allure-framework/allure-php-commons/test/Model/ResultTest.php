<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Model;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Model\Result;

/**
 * @covers \Qameta\Allure\Model\Result
 */
class ResultTest extends TestCase
{
    public function testGetUuid_ConstructedWithUuid_ReturnsSameValue(): void
    {
        $result = $this->getMockForAbstractClass(Result::class, ['a']);
        self::assertSame('a', $result->getUuid());
    }

    public function testGetExcluded_Constructed_ReturnsFalse(): void
    {
        $result = $this->getMockForAbstractClass(Result::class, ['a']);
        self::assertFalse($result->getExcluded());
    }

    /**
     * @param bool $excluded
     * @param bool $expectedData
     * @return void
     * @dataProvider providerSetExcluded
     */
    public function testGetExcluded_SetExcludedCalledWithGivenFlag_ReturnsSameValue(
        bool $excluded,
        bool $expectedData,
    ): void {
        $result = $this->getMockForAbstractClass(Result::class, ['a']);
        $result->setExcluded($excluded);
        self::assertSame($expectedData, $result->getExcluded());
    }

    /**
     * @return iterable<string, array{bool, bool}>
     */
    public static function providerSetExcluded(): iterable
    {
        return [
            'True' => [true, true],
            'False' => [false, false],
        ];
    }

    public function testSetExcluded_Always_ReturnsSelf(): void
    {
        $result = $this->getMockForAbstractClass(Result::class, ['a']);
        self::assertSame($result, $result->setExcluded());
    }
}
