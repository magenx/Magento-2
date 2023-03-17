<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Io;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Io\StringDataSource;
use Throwable;

use function fclose;
use function stream_get_contents;

/**
 * @covers \Qameta\Allure\Io\StringDataSource
 */
class StringDataSourceTest extends TestCase
{
    /**
     * @var list<resource>
     */
    private array $streams = [];

    public function tearDown(): void
    {
        foreach ($this->streams as $stream) {
            @fclose($stream);
        }
    }

    /**
     * @throws Throwable
     */
    public function testGetStream_ConstructedWithGivenData_ReturnsResourceWithSameData(): void
    {
        $attachment = new StringDataSource('a');
        $stream = $attachment->createStream();
        $this->streams[] = $stream;
        $actualData = stream_get_contents($stream);
        self::assertSame('a', $actualData);
    }
}
