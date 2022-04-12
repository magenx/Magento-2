<?php

namespace Yandex\Allure\Adapter\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 * @package Yandex\Allure\Adapter\Annotation
 */
class Issues
{
    /**
     * @var array
     * @Required
     */
    public $issueKeys;

    /**
     * @return array
     */
    public function getIssueKeys()
    {
        return $this->issueKeys;
    }
}
