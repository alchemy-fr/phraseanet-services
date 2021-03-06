<?php

declare(strict_types=1);

namespace Alchemy\RemoteAuthBundle\Model;

use Symfony\Component\Security\Core\User\UserInterface;

class RemoteUser implements UserInterface
{
    private string $username;
    private string $id;
    private array $roles;
    private array $groups;

    public function __construct(string $id, string $username, array $roles = [], array $groups = [])
    {
        $this->username = $username;
        $this->id = $id;
        $this->roles = $roles;
        $this->groups = $groups;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getGroups(): array
    {
        return $this->groups;
    }

    public function getGroupIds(): array
    {
        return array_keys($this->groups);
    }

    public function getPassword()
    {
        return null;
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->username;
    }

    public function eraseCredentials()
    {
    }
}
