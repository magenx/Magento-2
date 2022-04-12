<?php

namespace Yandex\Allure\Adapter\Event;

use Yandex\Allure\Adapter\Model\Attachment;
use Yandex\Allure\Adapter\Model\Entity;
use Yandex\Allure\Adapter\Model\Step;

class RemoveAttachmentsEvent implements StepEvent
{
    private $pattern;

    public function __construct($pattern)
    {
        $this->pattern = $pattern;
    }

    public function process(Entity $context)
    {
        if ($context instanceof Step) {
            foreach ($context->getAttachments() as $index => $attachment) {
                if ($attachment instanceof Attachment) {
                    $path = $attachment->getSource();
                    if (preg_match($this->pattern, $path)) {
                        if (file_exists($path) && is_writable($path)) {
                            unlink($path);
                        }
                        $context->removeAttachment($index);
                    }
                }
            }
        }
    }
}
