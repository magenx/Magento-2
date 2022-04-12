<?php

namespace Yandex\Allure\Adapter\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @package Yandex\Allure\Adapter\Annotation
 */
class Parameters
{
    /**
     * @var array<Yandex\Allure\Adapter\Annotation\Parameter>
     * @Required
     */
    public $parameters;
}
