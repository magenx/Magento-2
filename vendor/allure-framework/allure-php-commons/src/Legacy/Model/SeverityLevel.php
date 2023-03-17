<?php

namespace Yandex\Allure\Adapter\Model;

/**
 * Severity level
 *
 * @deprecated Used only with legacy annotation {@see \Yandex\Allure\Adapter\Annotation\Severity}.
 */
final class SeverityLevel
{
    public const BLOCKER = 'blocker';
    public const CRITICAL = 'critical';
    public const NORMAL = 'normal';
    public const MINOR = 'minor';
    public const TRIVIAL = 'trivial';
}
