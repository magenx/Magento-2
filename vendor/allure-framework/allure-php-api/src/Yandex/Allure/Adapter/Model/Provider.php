<?php

namespace Yandex\Allure\Adapter\Model;

class Provider
{

    /**
     * @var string
     */
    private static $outputDirectory;

    /**
     * @param string $outputDirectory
     */
    public static function setOutputDirectory($outputDirectory)
    {
        self::$outputDirectory = $outputDirectory;
    }

    /**
     * @return string
     */
    public static function getOutputDirectory()
    {
        return self::$outputDirectory;
    }
}
