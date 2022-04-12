<?php

namespace Yandex\Allure\Adapter\Annotation;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationRegistry;
use PHPUnit\Framework\TestCase;

class AnnotationProviderTest extends TestCase
{
    private const TYPE_CLASS = 'class';
    private const TYPE_METHOD = 'method';
    private const METHOD_NAME = 'methodWithAnnotations';

    public static function setUpBeforeClass(): void
    {
        AnnotationRegistry::registerFile(__DIR__ . '/Fixtures/TestAnnotation.php');
    }

    protected function tearDown(): void
    {
        AnnotationProvider::tearDown();
    }

    public function testGetClassAnnotations(): void
    {
        $instance = new Fixtures\ClassWithAnnotations();
        $annotations = AnnotationProvider::getClassAnnotations($instance);
        $this->assertTrue(sizeof($annotations) === 1);
        $annotation = array_pop($annotations);
        $this->assertInstanceOf('Yandex\Allure\Adapter\Annotation\Fixtures\TestAnnotation', $annotation);
        $this->assertEquals(self::TYPE_CLASS, $annotation->value);
    }

    public function testGetMethodAnnotations(): void
    {
        $instance = new Fixtures\ClassWithAnnotations();
        $annotations = AnnotationProvider::getMethodAnnotations($instance, self::METHOD_NAME);
        $this->assertTrue(sizeof($annotations) === 1);
        $annotation = array_pop($annotations);
        $this->assertInstanceOf('Yandex\Allure\Adapter\Annotation\Fixtures\TestAnnotation', $annotation);
        $this->assertEquals(self::TYPE_METHOD, $annotation->value);
    }

    public function testShouldThrowExceptionForNotImportedAnnotations(): void
    {
        $instance = new Fixtures\ClassWithIgnoreAnnotation();
        $this->expectException(AnnotationException::class);
        AnnotationProvider::getClassAnnotations($instance);
    }

    public function testShouldIgnoreGivenAnnotations(): void
    {
        $instance = new Fixtures\ClassWithIgnoreAnnotation();
        AnnotationProvider::addIgnoredAnnotations(['SomeCustomClassAnnotation', 'SomeCustomMethodAnnotation']);

        $this->assertEmpty(AnnotationProvider::getClassAnnotations($instance));
        $this->assertEmpty(AnnotationProvider::getMethodAnnotations($instance, 'methodWithIgnoredAnnotation'));
    }
}
