<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Io;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Io\SystemClock;

use function floatval;
use function time;
use function usleep;

/**
 * @covers \Qameta\Allure\Io\SystemClock
 */
class SystemClockTest extends TestCase
{
    public function testTime_CalledTwiceAfterPeriod_ResultsDifferenceIsSameAsPeriod(): void
    {
        $clock = new SystemClock();

        $timeBase = time();
        $firstValue = floatval($clock->now()->format('U.u')) - $timeBase;
        usleep(200000); // sleep for 0.2 s
        $secondValue = floatval($clock->now()->format('U.u')) - $timeBase;
        // Difference should be longer than at least half of the sleep time
        self::assertGreaterThanOrEqual(0.1, $secondValue - $firstValue);
    }
}
