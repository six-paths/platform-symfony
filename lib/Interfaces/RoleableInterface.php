<?php

namespace Sixpaths\Platform\Symfony\Interfaces;

interface RoleableInterface
{
    /**
     * @return array<string>
     */
    public function getRoles(): array;

    /**
     * @return boolean
     */
    public function hasRole(string $role): bool;

    /**
     * @param array<string> $roles
     *
     * @return boolean
     */
    public function hasOneOfRole(array $roles): bool;

    /**
     * Adds a role to roles.
     *
     * @param string $role
     *
     * @return \Sixpaths\Platform\Symfony\Interfaces\RoleableInterface
     */
    public function addRole(string $role): RoleableInterface;

    /**
     * Removes a role from roles.
     *
     * @param string $role
     *
     * @return \Sixpaths\Platform\Symfony\Interfaces\RoleableInterface
     */
    public function removeRole(string $role): RoleableInterface;

    /**
     * Sets the collection of roles.
     *
     * @param array<string> $roles
     *
     * @return \Sixpaths\Platform\Symfony\Interfaces\RoleableInterface
     */
    public function setRoles(array $roles): RoleableInterface;
}
