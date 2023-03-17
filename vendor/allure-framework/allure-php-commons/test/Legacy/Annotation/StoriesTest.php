<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Legacy\Annotation;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Test\Attribute\AnnotationTestTrait;
use Qameta\Allure\Attribute\Story;
use Yandex\Allure\Adapter\Annotation\Stories;

/**
 * @covers \Yandex\Allure\Adapter\Annotation\Stories
 */
class StoriesTest extends TestCase
{
    use AnnotationTestTrait;

    public function testGetStories_WithSingleValue_ReturnsSameValueInList(): void
    {
        $stories = $this->getStoriesInstance('demoWithSingleValue');
        self::assertSame(['a'], $stories->getStories());
    }

    public function testGetStories_WithTwoValues_ReturnsSameValuesInList(): void
    {
        $stories = $this->getStoriesInstance('demoWithTwoValues');
        self::assertSame(['a', 'b'], $stories->getStories());
    }

    public function testConvert_WithSingleValue_ResultHasSameValueInList(): void
    {
        $stories = $this->getStoriesInstance('demoWithSingleValue');
        $expectedList = [
            [
                'class' => Story::class,
                'name' => 'story',
                'value' => 'a',
            ],
        ];
        self::assertSame($expectedList, $this->exportLabels(...$stories->convert()));
    }

    public function testConvert_WithTwoValues_ResultHasSameValuesInList(): void
    {
        $stories = $this->getStoriesInstance('demoWithTwoValues');
        $expectedList = [
            [
                'class' => Story::class,
                'name' => 'story',
                'value' => 'a',
            ],
            [
                'class' => Story::class,
                'name' => 'story',
                'value' => 'b',
            ],
        ];
        self::assertSame($expectedList, $this->exportLabels(...$stories->convert()));
    }

    /**
     * @Stories("a")
     */
    protected function demoWithSingleValue(): void
    {
    }

    /**
     * @Stories("a","b")
     */
    protected function demoWithTwoValues(): void
    {
    }

    /**
     * @psalm-suppress DeprecatedClass
     */
    private function getStoriesInstance(string $methodName): Stories
    {
        return $this->getLegacyAttributeInstance(Stories::class, $methodName);
    }
}
