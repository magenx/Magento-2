<?php

declare(strict_types=1);

namespace Qameta\Allure\Hook;

use Qameta\Allure\Model\AttachmentResult;

interface AfterAttachmentWriteHookInterface extends LifecycleHookInterface
{
    public function afterAttachmentWrite(AttachmentResult $attachment): void;
}
