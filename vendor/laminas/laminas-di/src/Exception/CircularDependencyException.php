<?php

declare(strict_types=1);

namespace Laminas\Di\Exception;

use DomainException;

class CircularDependencyException extends DomainException implements ExceptionInterface
{
}
