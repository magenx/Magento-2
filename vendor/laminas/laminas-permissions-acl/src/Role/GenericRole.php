<?php

declare(strict_types=1);

namespace Laminas\Permissions\Acl\Role;

use Stringable;

class GenericRole implements RoleInterface, Stringable
{
    /**
     * Unique id of Role
     *
     * @var string
     */
    protected $roleId;

    /**
     * Sets the Role identifier
     *
     * @param string $roleId
     */
    public function __construct($roleId)
    {
        $this->roleId = (string) $roleId;
    }

    /**
     * Defined by RoleInterface; returns the Role identifier
     *
     * @return string
     */
    public function getRoleId()
    {
        return $this->roleId;
    }

    /**
     * Defined by RoleInterface; returns the Role identifier
     * Proxies to getRoleId()
     */
    public function __toString(): string
    {
        return $this->getRoleId();
    }
}
