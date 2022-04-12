<?php

declare(strict_types=1);

namespace Laminas\Di\Exception;

use Psr\Container\ContainerExceptionInterface;

class InvalidServiceConfigException extends LogicException implements ContainerExceptionInterface
{
}
