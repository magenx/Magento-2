<?php

declare(strict_types=1);

namespace Qameta\Allure\Internal;

use Psr\Log\LoggerInterface;
use Throwable;

/**
 * @internal
 */
trait LoggerAwareTrait
{
    private LoggerInterface $logger;

    private function logException(string $message, Throwable $exception, array $context = []): void
    {
        $reasons = [$message];
        $context['exception'] = $exception;
        while (isset($exception)) {
            $reasons[] = "{$exception->getMessage()} in {$exception->getFile()}:{$exception->getLine()}";
            $exception = $exception->getPrevious();
        }
        $reason = implode("\n\nCaused by:\n", $reasons);
        $this->logger->error($reason, $context);
    }

    private function logLastError(string $message, ?array $context): void
    {
        if (isset($context['message'])) {
            $message .= ': {message}';
        }
        $this->logger->error($message, $context ?? []);
    }
}
