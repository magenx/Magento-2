<?php

namespace Yandex\Allure\Adapter\Annotation\Fixtures;

/**
 * @TestAnnotation("class")
 */
class ClassWithAnnotations
{
    /**
     * @TestAnnotation("method")
     */
    public function methodWithAnnotations()
    {
    }
}
