<?php

namespace Yandex\Allure\Adapter\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;
use Yandex\Allure\Adapter\Model\ParameterKind;

/**
 * @Annotation
 * @Target({"METHOD", "ANNOTATION"})
 * @package Yandex\Allure\Adapter\Annotation
 */
class Label
{
    /**
     * @var string
     * @Required
     */
    public $name;

    /**
     * @var array
     * @Required
     */
    public $values;

}
