<?php

declare(strict_types=1);

namespace Qameta\Allure\Setup;

use Qameta\Allure\Model\LinkType;

final class LinkTemplateCollection implements LinkTemplateCollectionInterface
{
    /**
     * @param array<string, LinkTemplateInterface> $templateLinks
     */
    public function __construct(
        private array $templateLinks = [],
    ) {
    }

    public function get(LinkType $type): ?LinkTemplateInterface
    {
        return $this->templateLinks[(string) $type] ?? null;
    }
}
