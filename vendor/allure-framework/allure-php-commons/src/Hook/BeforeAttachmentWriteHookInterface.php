<?php

declare(strict_types=1);

namespace Qameta\Allure\Hook;

use Qameta\Allure\Model\AttachmentResult;

interface BeforeAttachmentWriteHookInterface extends LifecycleHookInterface
{
    public function beforeAttachmentWrite(AttachmentResult $attachment): void;
}
