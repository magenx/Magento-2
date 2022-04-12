<?php

declare(strict_types=1);

namespace Laminas\Di\Exception;

use DomainException;

class UndefinedReferenceException extends DomainException implements ExceptionInterface
{
}
