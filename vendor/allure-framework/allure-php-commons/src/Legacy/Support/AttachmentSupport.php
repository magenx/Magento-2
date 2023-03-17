<?php

declare(strict_types=1);

namespace Yandex\Allure\Adapter\Support;

use Qameta\Allure\Allure;

use function file_exists;
use function is_file;

/**
 * @deprecated Please use {@see Allure::attachment()} and {@see Allure::attachmentFile()} methods directly instead
 *             of this trait.
 */
trait AttachmentSupport
{
    /**
     * Adds a new attachment to report
     *
     * @param string      $filePathOrContents either a string with file contents or file path to copy
     * @param string      $caption
     * @param string|null $type
     */
    public function addAttachment(string $filePathOrContents, string $caption, ?string $type = null): void
    {
        if (@file_exists($filePathOrContents) && is_file($filePathOrContents)) {
            Allure::attachmentFile($caption, $filePathOrContents, $type);
        } else {
            Allure::attachment($caption, $filePathOrContents, $type);
        }
    }
}
