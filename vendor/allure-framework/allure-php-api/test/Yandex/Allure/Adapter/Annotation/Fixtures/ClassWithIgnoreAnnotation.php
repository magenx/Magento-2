<?php

namespace Yandex\Allure\Adapter\Annotation\Fixtures;

/**
 * @SomeCustomClassAnnotation("foo")
 */
class ClassWithIgnoreAnnotation
{
    /**
     * @SomeCustomMethodAnnotation("bar")
     */
    public function methodWithIgnoredAnnotation()
    {
    }
}
