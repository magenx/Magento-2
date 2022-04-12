<?php

namespace Yandex\Allure\Adapter\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @package Yandex\Allure\Adapter\Annotation
 */
class Labels
{
    /**
     * @var array<Yandex\Allure\Adapter\Annotation\Label>
     * @Required
     */
    public $labels;
}
