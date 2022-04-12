<?php

namespace Yandex\Allure\Adapter\Model;

use Yandex\Allure\Adapter\AllureException;

/**
 * @package Yandex\Allure\Adapter\Model
 */
class ConstantChecker
{

    /**
     * Checks whether constant with the specified value is present. If it's present it's returned. An
     * exception is thrown otherwise.
     * @param $className
     * @param $value
     * @throws AllureException
     * @return
     */
    public static function validate($className, $value)
    {
        $ref = new \ReflectionClass($className);
        foreach ($ref->getConstants() as $constantValue) {
            if ($constantValue === $value) {
                return $value;
            }
        }
        throw new AllureException(
            "Value \"$value\" is not present in class $className. You should use a constant from this class."
        );
    }
}
