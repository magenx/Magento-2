<?php

declare(strict_types=1);

namespace Qameta\Allure\Exception;

use Throwable;

use function gettype;
use function is_object;

final class InvalidMethodNameException extends \DomainException
{
    public function __construct(
        private mixed $methodName,
        Throwable $previous = null,
    ) {
        $methodDescription = gettype($this->methodName);
        if (is_object($this->methodName)) {
            $methodDescription .= '(' . $this->methodName::class . ')';
        }

        parent::__construct("Invalid method name: <$methodDescription>", 0, $previous);
    }

    public function getMethodName(): mixed
    {
        return $this->methodName;
    }
}
