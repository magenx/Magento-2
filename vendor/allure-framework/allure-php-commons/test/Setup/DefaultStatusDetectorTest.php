<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Setup;

use Exception;
use PHPUnit\Framework\TestCase;
use Qameta\Allure\Model\Status;
use Qameta\Allure\Setup\DefaultStatusDetector;

/**
 * @covers \Qameta\Allure\Setup\DefaultStatusDetector
 */
class DefaultStatusDetectorTest extends TestCase
{
    public function testGetStatus_Always_ReturnsFailedStatus(): void
    {
        $detector = new DefaultStatusDetector();
        self::assertSame(Status::broken(), $detector->getStatus(new Exception()));
    }

    public function testGetStatusDetails_GivenError_ReturnsMatchingDetails(): void
    {
        $detector = new DefaultStatusDetector();
        $error = new Exception('abracadabra', 1);
        $details = $detector->getStatusDetails($error);
        self::assertStringContainsString('abracadabra', $details?->getMessage() ?? '');
        self::assertStringContainsString('Exception(1)', $details?->getMessage() ?? '');
        self::assertSame($error->getTraceAsString(), $details?->getTrace());
    }
}
