<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public function __construct(
        private readonly int $id,
        private readonly string $username,
        private readonly string $email,
        private readonly bool $active,
        private readonly ?string $password = null,
        private readonly ?string $token = null,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getToken(): string
    {
        return $this->token ?? throw new \RuntimeException('Token is not set.');
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUserIdentifier(): string
    {
        $identifier = $this->getUsername();
        if ('' === $identifier) {
            throw new \RuntimeException('User identifier cannot be empty.');
        }

        return $identifier;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }
}
