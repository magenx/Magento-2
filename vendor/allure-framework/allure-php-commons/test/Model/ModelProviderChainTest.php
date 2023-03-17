<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Model;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Model\Label;
use Qameta\Allure\Model\Link;
use Qameta\Allure\Model\ModelProviderChain;
use Qameta\Allure\Model\ModelProviderInterface;
use Qameta\Allure\Model\Parameter;

/**
 * @covers \Qameta\Allure\Model\ModelProviderChain
 */
class ModelProviderChainTest extends TestCase
{
    public function testGetLinks_ConstructedWithoutProviders_ReturnsEmptyList(): void
    {
        $chain = new ModelProviderChain();
        self::assertEmpty($chain->getLinks());
    }

    public function testGetLinks_OnlySecondProviderReturnsLinks_ReturnsSameLinks(): void
    {
        $firstProvider = $this->createStub(ModelProviderInterface::class);
        $secondProvider = $this->createStub(ModelProviderInterface::class);
        $linkA = new Link();
        $linkB = new Link();
        $secondProvider
            ->method('getLinks')
            ->willReturn([$linkA, $linkB]);
        $chain = new ModelProviderChain($firstProvider, $secondProvider);
        self::assertSame([$linkA, $linkB], $chain->getLinks());
    }

    public function testGetLinks_OnlyFirstProviderReturnsLinks_ReturnsSameLinks(): void
    {
        $firstProvider = $this->createStub(ModelProviderInterface::class);
        $secondProvider = $this->createStub(ModelProviderInterface::class);
        $linkA = new Link();
        $linkB = new Link();
        $firstProvider
            ->method('getLinks')
            ->willReturn([$linkA, $linkB]);
        $chain = new ModelProviderChain($firstProvider, $secondProvider);
        self::assertSame([$linkA, $linkB], $chain->getLinks());
    }

    public function testGetLinks_BothProvidersReturnLinks_ReturnsMergedLinks(): void
    {
        $firstProvider = $this->createStub(ModelProviderInterface::class);
        $secondProvider = $this->createStub(ModelProviderInterface::class);
        $linkA = new Link();
        $linkB = new Link();
        $firstProvider
            ->method('getLinks')
            ->willReturn([$linkA]);
        $secondProvider
            ->method('getLinks')
            ->willReturn([$linkB]);
        $chain = new ModelProviderChain($firstProvider, $secondProvider);
        self::assertSame([$linkA, $linkB], $chain->getLinks());
    }

    public function testGetLabels_ConstructedWithoutProviders_ReturnsEmptyList(): void
    {
        $chain = new ModelProviderChain();
        self::assertEmpty($chain->getLabels());
    }

    public function testGetLabels_OnlySecondProviderReturnsLabels_ReturnsSameLabels(): void
    {
        $firstProvider = $this->createStub(ModelProviderInterface::class);
        $secondProvider = $this->createStub(ModelProviderInterface::class);
        $labelA = new Label();
        $labelB = new Label();
        $secondProvider
            ->method('getLabels')
            ->willReturn([$labelA, $labelB]);
        $chain = new ModelProviderChain($firstProvider, $secondProvider);
        self::assertSame([$labelA, $labelB], $chain->getLabels());
    }

    public function testGetLabels_OnlyFirstProviderReturnsLabels_ReturnsSameLabels(): void
    {
        $firstProvider = $this->createStub(ModelProviderInterface::class);
        $secondProvider = $this->createStub(ModelProviderInterface::class);
        $labelA = new Label();
        $labelB = new Label();
        $firstProvider
            ->method('getLabels')
            ->willReturn([$labelA, $labelB]);
        $chain = new ModelProviderChain($firstProvider, $secondProvider);
        self::assertSame([$labelA, $labelB], $chain->getLabels());
    }

    public function testGetLabels_BothProvidersReturnLabels_ReturnsMergedLabels(): void
    {
        $firstProvider = $this->createStub(ModelProviderInterface::class);
        $secondProvider = $this->createStub(ModelProviderInterface::class);
        $labelA = new Label();
        $labelB = new Label();
        $firstProvider
            ->method('getLabels')
            ->willReturn([$labelA]);
        $secondProvider
            ->method('getLabels')
            ->willReturn([$labelB]);
        $chain = new ModelProviderChain($firstProvider, $secondProvider);
        self::assertSame([$labelA, $labelB], $chain->getLabels());
    }

    public function testGetParameters_ConstructedWithoutProviders_ReturnsEmptyList(): void
    {
        $chain = new ModelProviderChain();
        self::assertEmpty($chain->getParameters());
    }

    public function testGetParameters_OnlySecondProviderReturnsParameters_ReturnsSameParameters(): void
    {
        $firstProvider = $this->createStub(ModelProviderInterface::class);
        $secondProvider = $this->createStub(ModelProviderInterface::class);
        $parameterA = new Parameter('a');
        $parameterB = new Parameter('b');
        $secondProvider
            ->method('getParameters')
            ->willReturn([$parameterA, $parameterB]);
        $chain = new ModelProviderChain($firstProvider, $secondProvider);
        self::assertSame([$parameterA, $parameterB], $chain->getParameters());
    }

    public function testGetParameters_OnlyFirstProviderReturnsParameters_ReturnsSameParameters(): void
    {
        $firstProvider = $this->createStub(ModelProviderInterface::class);
        $secondProvider = $this->createStub(ModelProviderInterface::class);
        $parameterA = new Parameter('a');
        $parameterB = new Parameter('b');
        $firstProvider
            ->method('getParameters')
            ->willReturn([$parameterA, $parameterB]);
        $chain = new ModelProviderChain($firstProvider, $secondProvider);
        self::assertSame([$parameterA, $parameterB], $chain->getParameters());
    }

    public function testGetParameters_BothProvidersReturnParameters_ReturnsMergedParameters(): void
    {
        $firstProvider = $this->createStub(ModelProviderInterface::class);
        $secondProvider = $this->createStub(ModelProviderInterface::class);
        $parameterA = new Parameter('a');
        $parameterB = new Parameter('b');
        $firstProvider
            ->method('getParameters')
            ->willReturn([$parameterA]);
        $secondProvider
            ->method('getParameters')
            ->willReturn([$parameterB]);
        $chain = new ModelProviderChain($firstProvider, $secondProvider);
        self::assertSame([$parameterA, $parameterB], $chain->getParameters());
    }

    public function testGetDisplayName_ConstructedWithoutProviders_ReturnsNull(): void
    {
        $chain = new ModelProviderChain();
        self::assertNull($chain->getDisplayName());
    }

    public function testGetDisplayName_BothProvidersProvideNullName_ReturnsNull(): void
    {
        $chain = new ModelProviderChain(
            $this->createStub(ModelProviderInterface::class),
            $this->createStub(ModelProviderInterface::class),
        );
        self::assertNull($chain->getDisplayName());
    }

    public function testGetDisplayName_OnlyFirstProviderProvidesNonNullName_ReturnsSameName(): void
    {
        $firstProvider = $this->createStub(ModelProviderInterface::class);
        $firstProvider
            ->method('getDisplayName')
            ->willReturn('a');
        $secondProvider = $this->createStub(ModelProviderInterface::class);
        $chain = new ModelProviderChain($firstProvider, $secondProvider);
        self::assertSame('a', $chain->getDisplayName());
    }

    public function testGetDisplayName_OnlySecondProviderProvidesNonNullName_ReturnsSameName(): void
    {
        $firstProvider = $this->createStub(ModelProviderInterface::class);
        $secondProvider = $this->createStub(ModelProviderInterface::class);
        $secondProvider
            ->method('getDisplayName')
            ->willReturn('a');
        $chain = new ModelProviderChain($firstProvider, $secondProvider);
        self::assertSame('a', $chain->getDisplayName());
    }

    public function testGetDisplayName_BothProvidersProvideNonNullName_ReturnsNameFromFirstProvider(): void
    {
        $firstProvider = $this->createStub(ModelProviderInterface::class);
        $firstProvider
            ->method('getDisplayName')
            ->willReturn('a');
        $secondProvider = $this->createStub(ModelProviderInterface::class);
        $secondProvider
            ->method('getDisplayName')
            ->willReturn('b');
        $chain = new ModelProviderChain($firstProvider, $secondProvider);
        self::assertSame('a', $chain->getDisplayName());
    }

    public function testGetDescription_ConstructedWithoutProviders_ReturnsNull(): void
    {
        $chain = new ModelProviderChain();
        self::assertNull($chain->getDescription());
    }

    public function testGetDescription_BothProvidersProvideNullDescription_ReturnsNull(): void
    {
        $chain = new ModelProviderChain(
            $this->createStub(ModelProviderInterface::class),
            $this->createStub(ModelProviderInterface::class),
        );
        self::assertNull($chain->getDescription());
    }

    public function testGetDescription_OnlyFirstProviderProvidesNonNullValue_ReturnsSameValue(): void
    {
        $firstProvider = $this->createStub(ModelProviderInterface::class);
        $firstProvider
            ->method('getDescription')
            ->willReturn('a');
        $secondProvider = $this->createStub(ModelProviderInterface::class);
        $chain = new ModelProviderChain($firstProvider, $secondProvider);
        self::assertSame('a', $chain->getDescription());
    }

    public function testGetDescription_OnlySecondProviderProvidesNonNullValue_ReturnsSameName(): void
    {
        $firstProvider = $this->createStub(ModelProviderInterface::class);
        $secondProvider = $this->createStub(ModelProviderInterface::class);
        $secondProvider
            ->method('getDescription')
            ->willReturn('a');
        $chain = new ModelProviderChain($firstProvider, $secondProvider);
        self::assertSame('a', $chain->getDescription());
    }

    public function testGetDescription_BothProvidersProvideNonNullValue_ReturnsValueFromFirstProvider(): void
    {
        $firstProvider = $this->createStub(ModelProviderInterface::class);
        $firstProvider
            ->method('getDescription')
            ->willReturn('a');
        $secondProvider = $this->createStub(ModelProviderInterface::class);
        $secondProvider
            ->method('getDescription')
            ->willReturn('b');
        $chain = new ModelProviderChain($firstProvider, $secondProvider);
        self::assertSame('a', $chain->getDescription());
    }

    public function testGetDescriptionHtml_ConstructedWithoutProviders_ReturnsNull(): void
    {
        $chain = new ModelProviderChain();
        self::assertNull($chain->getDescriptionHtml());
    }

    public function testGetDescriptionHtml_BothProvidersProvideNullDescription_ReturnsNull(): void
    {
        $chain = new ModelProviderChain(
            $this->createStub(ModelProviderInterface::class),
            $this->createStub(ModelProviderInterface::class),
        );
        self::assertNull($chain->getDescriptionHtml());
    }

    public function testGetDescriptionHtml_OnlyFirstProviderProvidesNonNullValue_ReturnsSameValue(): void
    {
        $firstProvider = $this->createStub(ModelProviderInterface::class);
        $firstProvider
            ->method('getDescriptionHtml')
            ->willReturn('a');
        $secondProvider = $this->createStub(ModelProviderInterface::class);
        $chain = new ModelProviderChain($firstProvider, $secondProvider);
        self::assertSame('a', $chain->getDescriptionHtml());
    }

    public function testGetDescriptionHtml_OnlySecondProviderProvidesNonNullValue_ReturnsSameName(): void
    {
        $firstProvider = $this->createStub(ModelProviderInterface::class);
        $secondProvider = $this->createStub(ModelProviderInterface::class);
        $secondProvider
            ->method('getDescriptionHtml')
            ->willReturn('a');
        $chain = new ModelProviderChain($firstProvider, $secondProvider);
        self::assertSame('a', $chain->getDescriptionHtml());
    }

    public function testGetDescriptionHtml_BothProvidersProvideNonNullValue_ReturnsValueFromFirstProvider(): void
    {
        $firstProvider = $this->createStub(ModelProviderInterface::class);
        $firstProvider
            ->method('getDescriptionHtml')
            ->willReturn('a');
        $secondProvider = $this->createStub(ModelProviderInterface::class);
        $secondProvider
            ->method('getDescriptionHtml')
            ->willReturn('b');
        $chain = new ModelProviderChain($firstProvider, $secondProvider);
        self::assertSame('a', $chain->getDescriptionHtml());
    }

    public function testGetFullName_ConstructedWithoutProviders_ReturnsNull(): void
    {
        $chain = new ModelProviderChain();
        self::assertNull($chain->getFullName());
    }

    public function testGetFullName_BothProvidersProvideNullFullName_ReturnsNull(): void
    {
        $chain = new ModelProviderChain(
            $this->createStub(ModelProviderInterface::class),
            $this->createStub(ModelProviderInterface::class),
        );
        self::assertNull($chain->getFullName());
    }

    public function testGetFullName_OnlyFirstProviderProvidesNonNullValue_ReturnsSameValue(): void
    {
        $firstProvider = $this->createStub(ModelProviderInterface::class);
        $firstProvider
            ->method('getFullName')
            ->willReturn('a');
        $secondProvider = $this->createStub(ModelProviderInterface::class);
        $chain = new ModelProviderChain($firstProvider, $secondProvider);
        self::assertSame('a', $chain->getFullName());
    }

    public function testGetFullName_OnlySecondProviderProvidesNonNullValue_ReturnsSameName(): void
    {
        $firstProvider = $this->createStub(ModelProviderInterface::class);
        $secondProvider = $this->createStub(ModelProviderInterface::class);
        $secondProvider
            ->method('getFullName')
            ->willReturn('a');
        $chain = new ModelProviderChain($firstProvider, $secondProvider);
        self::assertSame('a', $chain->getFullName());
    }

    public function testGetFullName_BothProvidersProvideNonNullValue_ReturnsValueFromFirstProvider(): void
    {
        $firstProvider = $this->createStub(ModelProviderInterface::class);
        $firstProvider
            ->method('getFullName')
            ->willReturn('a');
        $secondProvider = $this->createStub(ModelProviderInterface::class);
        $secondProvider
            ->method('getFullName')
            ->willReturn('b');
        $chain = new ModelProviderChain($firstProvider, $secondProvider);
        self::assertSame('a', $chain->getFullName());
    }
}
