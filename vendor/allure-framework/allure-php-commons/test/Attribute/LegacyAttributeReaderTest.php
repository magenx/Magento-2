<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Attribute;

use Doctrine\Common\Annotations\AnnotationReader as DoctrineReader;
use Doctrine\Common\Annotations\Reader;
use PHPUnit\Framework\TestCase;
use Qameta\Allure\Attribute\AttributeReader;
use Qameta\Allure\Attribute\AttributeReaderInterface;
use Qameta\Allure\Attribute\Description;
use Qameta\Allure\Attribute\DisplayName;
use Qameta\Allure\Attribute\Feature;
use Qameta\Allure\Attribute\LabelInterface;
use Qameta\Allure\Attribute\LegacyAttributeReader;
use Qameta\Allure\Attribute\Story;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use Yandex\Allure\Adapter\Annotation\Description as LegacyDescription;
use Yandex\Allure\Adapter\Annotation\Title;

use function array_map;

/**
 * @covers \Qameta\Allure\Attribute\LegacyAttributeReader
 */
class LegacyAttributeReaderTest extends TestCase
{
    protected mixed $demoNoAnnotations = null;

    #[NativePropertyAttribute("a")]
    #[NativePropertyAttribute("b")]
    #[AnotherNativePropertyAttribute("c")]
    protected mixed $demoOnlyNativeAnnotations = null;

    /**
     * @LegacyPropertyAnnotation("a")
     * @LegacyPropertyAnnotation("b")
     * @AnotherLegacyPropertyAnnotation("c")
     */
    protected mixed $demoOnlyLegacyAnnotations = null;

    /**
     * @LegacyPropertyAnnotation("a")
     * @LegacyPropertyAnnotation("b")
     * @AnotherLegacyPropertyAnnotation("c")
     */
    #[NativePropertyAttribute("d")]
    #[NativePropertyAttribute("e")]
    #[AnotherNativePropertyAttribute("f")]
    protected mixed $demoMixedAnnotations = null;

    /**
     * @throws ReflectionException
     */
    public function testGetPropertyAnnotations_NoAnnotationsWithoutName_ReturnsEmptyArray(): void
    {
        $reader = new LegacyAttributeReader(new DoctrineReader(), new AttributeReader());
        $annotations = $reader->getPropertyAnnotations(
            new ReflectionProperty($this, 'demoNoAnnotations'),
        );
        self::assertEmpty($annotations);
    }

    /**
     * @throws ReflectionException
     */
    public function testGetPropertyAnnotations_NoAnnotationsWithName_ReturnsEmptyArray(): void
    {
        $reader = new LegacyAttributeReader(new DoctrineReader(), new AttributeReader());
        $annotations = $reader->getPropertyAnnotations(
            new ReflectionProperty($this, 'demoNoAnnotations'),
            NativePropertyAttribute::class,
        );
        self::assertEmpty($annotations);
    }

    /**
     * @throws ReflectionException
     */
    public function testGetPropertyAnnotations_OnlyNativeAnnotationsWithoutName_ReturnsMatchingList(): void
    {
        $reader = new LegacyAttributeReader(new DoctrineReader(), new AttributeReader());
        $annotations = $reader->getPropertyAnnotations(
            new ReflectionProperty($this, 'demoOnlyNativeAnnotations'),
        );
        $expectedList = [
            ['class' => NativePropertyAttribute::class, 'value' => 'a'],
            ['class' => NativePropertyAttribute::class, 'value' => 'b'],
            ['class' => AnotherNativePropertyAttribute::class, 'value' => 'c'],
        ];
        self::assertSame($expectedList, $this->exportAnnotations(...$annotations));
    }

    /**
     * @throws ReflectionException
     */
    public function testGetPropertyAnnotations_OnlyNativeAnnotationsWithName_ReturnsMatchingList(): void
    {
        $reader = new LegacyAttributeReader(new DoctrineReader(), new AttributeReader());
        $annotations = $reader->getPropertyAnnotations(
            new ReflectionProperty($this, 'demoOnlyNativeAnnotations'),
            NativePropertyAttribute::class,
        );
        $expectedList = [
            ['class' => NativePropertyAttribute::class, 'value' => 'a'],
            ['class' => NativePropertyAttribute::class, 'value' => 'b'],
        ];
        self::assertSame($expectedList, $this->exportAnnotations(...$annotations));
    }

    /**
     * @throws ReflectionException
     */
    public function testGetPropertyAnnotations_OnlyLegacyAnnotationsWithoutName_ReturnsMatchingList(): void
    {
        $reader = new LegacyAttributeReader(new DoctrineReader(), new AttributeReader());
        $annotations = $reader->getPropertyAnnotations(
            new ReflectionProperty($this, 'demoOnlyLegacyAnnotations'),
        );
        $expectedList = [
            ['class' => NativePropertyAttribute::class, 'value' => 'b'],
            ['class' => AnotherNativePropertyAttribute::class, 'value' => 'c'],
        ];
        self::assertSame($expectedList, $this->exportAnnotations(...$annotations));
    }

    /**
     * @throws ReflectionException
     */
    public function testGetPropertyAnnotations_OnlyLegacyAnnotationsWithName_ReturnsMatchingList(): void
    {
        $reader = new LegacyAttributeReader(new DoctrineReader(), new AttributeReader());
        $annotations = $reader->getPropertyAnnotations(
            new ReflectionProperty($this, 'demoOnlyLegacyAnnotations'),
            NativePropertyAttribute::class,
        );
        $expectedList = [
            ['class' => NativePropertyAttribute::class, 'value' => 'b'],
        ];
        self::assertSame($expectedList, $this->exportAnnotations(...$annotations));
    }

    /**
     * @throws ReflectionException
     */
    public function testGetPropertyAnnotations_MixedAnnotationsWithoutName_ReturnsMatchingList(): void
    {
        $reader = new LegacyAttributeReader(new DoctrineReader(), new AttributeReader());
        $annotations = $reader->getPropertyAnnotations(
            new ReflectionProperty($this, 'demoMixedAnnotations'),
        );
        $expectedList = [
            ['class' => NativePropertyAttribute::class, 'value' => 'b'],
            ['class' => AnotherNativePropertyAttribute::class, 'value' => 'c'],
            ['class' => NativePropertyAttribute::class, 'value' => 'd'],
            ['class' => NativePropertyAttribute::class, 'value' => 'e'],
            ['class' => AnotherNativePropertyAttribute::class, 'value' => 'f'],
        ];
        self::assertSame($expectedList, $this->exportAnnotations(...$annotations));
    }

    /**
     * @throws ReflectionException
     */
    public function testGetPropertyAnnotations_MixedAnnotationsWithName_ReturnsMatchingList(): void
    {
        $reader = new LegacyAttributeReader(new DoctrineReader(), new AttributeReader());
        $annotations = $reader->getPropertyAnnotations(
            new ReflectionProperty($this, 'demoMixedAnnotations'),
            NativePropertyAttribute::class,
        );
        $expectedList = [
            ['class' => NativePropertyAttribute::class, 'value' => 'b'],
            ['class' => NativePropertyAttribute::class, 'value' => 'd'],
            ['class' => NativePropertyAttribute::class, 'value' => 'e'],
        ];
        self::assertSame($expectedList, $this->exportAnnotations(...$annotations));
    }

    /**
     * @throws ReflectionException
     */
    public function testGetMethodAnnotations_NoAnnotationsWithoutName_ReturnsEmptyArray(): void
    {
        $reader = new LegacyAttributeReader(new DoctrineReader(), new AttributeReader());
        $annotations = $reader->getMethodAnnotations(
            new ReflectionMethod($this, 'demoNoAnnotations'),
        );
        self::assertEmpty($annotations);
    }

    /**
     * @throws ReflectionException
     */
    public function testGetMethodAnnotations_NoAnnotationsWithName_ReturnsEmptyArray(): void
    {
        $reader = new LegacyAttributeReader(new DoctrineReader(), new AttributeReader());
        $annotations = $reader->getMethodAnnotations(
            new ReflectionMethod($this, 'demoNoAnnotations'),
            NativePropertyAttribute::class,
        );
        self::assertEmpty($annotations);
    }

    /**
     * @throws ReflectionException
     */
    public function testGetMethodAnnotations_OnlyNativeAnnotationsWithoutName_ReturnsMatchingList(): void
    {
        $reader = new LegacyAttributeReader(new DoctrineReader(), new AttributeReader());
        $annotations = $reader->getMethodAnnotations(
            new ReflectionMethod($this, 'demoOnlyNativeAnnotations'),
        );
        $expectedList = [
            ['class' => Story::class, 'name' => 'story', 'value' => 'a'],
            ['class' => Story::class, 'name' => 'story', 'value' => 'b'],
            ['class' => Feature::class, 'name' => 'feature', 'value' => 'c'],
        ];
        self::assertSame($expectedList, $this->exportAnnotations(...$annotations));
    }

    /**
     * @throws ReflectionException
     */
    public function testGetMethodAnnotations_OnlyNativeAnnotationsWithName_ReturnsMatchingList(): void
    {
        $reader = new LegacyAttributeReader(new DoctrineReader(), new AttributeReader());
        $annotations = $reader->getMethodAnnotations(
            new ReflectionMethod($this, 'demoOnlyNativeAnnotations'),
            Story::class,
        );
        $expectedList = [
            ['class' => Story::class, 'name' => 'story', 'value' => 'a'],
            ['class' => Story::class, 'name' => 'story', 'value' => 'b'],
        ];
        self::assertSame($expectedList, $this->exportAnnotations(...$annotations));
    }

    /**
     * @throws ReflectionException
     */
    public function testGetMethodAnnotations_OnlyLegacyAnnotationsWithoutName_ReturnsMatchingList(): void
    {
        $reader = new LegacyAttributeReader(new DoctrineReader(), new AttributeReader());
        $annotations = $reader->getMethodAnnotations(new ReflectionMethod($this, 'demoOnlyLegacyAnnotations'));
        /** @psalm-suppress DeprecatedClass */
        $expectedList = [
            ['class' => DisplayName::class, 'value' => 'b'],
            ['class' => Description::class, 'value' => 'c'],
        ];
        self::assertSame($expectedList, $this->exportAnnotations(...$annotations));
    }

    private function exportAnnotations(object ...$annotations): array
    {
        return array_map(
            fn (object $annotation) => $this->exportAnnotation($annotation),
            $annotations,
        );
    }

    private function exportAnnotation(object $annotation): array
    {
        $data = [
            'class' => $annotation::class,
        ];
        if ($annotation instanceof NativePropertyAttribute) {
            $data['value'] = $annotation->getValue();
        }
        if ($annotation instanceof AnotherNativePropertyAttribute) {
            $data['value'] = $annotation->getValue();
        }
        if ($annotation instanceof DisplayName) {
            $data['value'] = $annotation->getValue();
        }
        if ($annotation instanceof Description) {
            $data['value'] = $annotation->getValue();
        }
        if ($annotation instanceof LabelInterface) {
            $data['name'] = $annotation->getName();
            $data['value'] = $annotation->getValue();
        }

        return $data;
    }

    protected function demoNoAnnotations(): void
    {
    }

    #[Story("a")]
    #[Story("b")]
    #[Feature("c")]
    protected function demoOnlyNativeAnnotations(): void
    {
    }

    /**
     * @Title("a")
     * @Title("b")
     * @LegacyDescription("c")
     */
    protected function demoOnlyLegacyAnnotations(): void
    {
    }

    /**
     * @Title("a")
     * @Title("b")
     * @LegacyDescription("c")
     */
    #[Story("d")]
    #[Story("e")]
    #[Feature("f")]
    protected function demoMixedAnnotations(): void
    {
    }

    public function testGetEnvironmentAnnotations_Always_ReturnsEmptyList(): void
    {
        $legacyReader = new LegacyAttributeReader(
            $this->createStub(Reader::class),
            $this->createStub(AttributeReaderInterface::class),
        );

        self::assertEmpty($legacyReader->getEnvironmentAnnotations([]));
    }
}
