<?php

namespace Yandex\Allure\Adapter\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 * @package Yandex\Allure\Adapter\Annotation
 */
class Epics
{
    /**
     * @var array
     * @Required
     */
    public $epicNames;

    /**
     * @return array
     */
    public function getEpicNames()
    {
        return $this->epicNames;
    }
}
