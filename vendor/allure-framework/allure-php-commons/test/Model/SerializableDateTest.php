<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Model;

use DateTimeImmutable;
use Exception;
use PHPUnit\Framework\TestCase;
use Qameta\Allure\Model\SerializableDate;

use function json_encode;

/**
 * @covers \Qameta\Allure\Model\SerializableDate
 */
class SerializableDateTest extends TestCase
{
    public function testGetDate_ConstructedWithDate_ReturnsSameInstance(): void
    {
        $date = new DateTimeImmutable();
        $serializableDate = new SerializableDate($date);
        self::assertSame($date, $serializableDate->getDate());
    }

    /**
     * @param string $dateTime
     * @param string $encodedJson
     * @return void
     * @dataProvider providerJsonSerialize
     * @throws Exception
     */
    public function testJsonSerialize_ConstructedWithDate_ReturnsMatchingValue(
        string $dateTime,
        string $encodedJson,
    ): void {
        $date = new DateTimeImmutable($dateTime);
        $serializableDate = new SerializableDate($date);
        self::assertSame($encodedJson, json_encode($serializableDate));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function providerJsonSerialize(): iterable
    {
        return [
            'Positive timestamp' => ['@1', '1000'],
            'Negative timestamp' => ['@-1', '-1000'],
            'Zero timestamp' => ['@0', '0'],
            'Positive timestamp with minimal microseconds' => ['@1.001', '1001'],
            'Positive timestamp with maximal microseconds' => ['@1.999', '1999'],
            'Positive timestamp with exceeding microseconds' => ['@1.23456', '1234'],
        ];
    }
}
