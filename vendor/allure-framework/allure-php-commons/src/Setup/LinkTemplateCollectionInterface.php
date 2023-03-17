<?php

declare(strict_types=1);

namespace Qameta\Allure\Setup;

use Qameta\Allure\Model\LinkType;

interface LinkTemplateCollectionInterface
{
    public function get(LinkType $type): ?LinkTemplateInterface;
}
