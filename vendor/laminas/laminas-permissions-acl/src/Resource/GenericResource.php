<?php

declare(strict_types=1);

namespace Laminas\Permissions\Acl\Resource;

use Stringable;

class GenericResource implements ResourceInterface, Stringable
{
    /**
     * Unique id of Resource
     *
     * @var string
     */
    protected $resourceId;

    /**
     * Sets the Resource identifier
     *
     * @param  string $resourceId
     */
    public function __construct($resourceId)
    {
        $this->resourceId = (string) $resourceId;
    }

    /**
     * Defined by ResourceInterface; returns the Resource identifier
     *
     * @return string
     */
    public function getResourceId()
    {
        return $this->resourceId;
    }

    /**
     * Defined by ResourceInterface; returns the Resource identifier
     * Proxies to getResourceId()
     */
    public function __toString(): string
    {
        return $this->getResourceId();
    }
}
