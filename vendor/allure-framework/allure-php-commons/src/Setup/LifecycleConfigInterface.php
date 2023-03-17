<?php

declare(strict_types=1);

namespace Qameta\Allure\Setup;

use Qameta\Allure\Model\ResultFactoryInterface;

interface LifecycleConfigInterface
{
    public function getResultFactory(): ResultFactoryInterface;

    public function getStatusDetector(): StatusDetectorInterface;

    public function getLinkTemplates(): LinkTemplateCollectionInterface;
}
