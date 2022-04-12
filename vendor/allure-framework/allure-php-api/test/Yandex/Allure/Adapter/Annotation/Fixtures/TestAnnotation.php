<?php

namespace Yandex\Allure\Adapter\Annotation\Fixtures;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class TestAnnotation
{
    /**
     * @var string
     * @Required
     */
    public $value;
}
