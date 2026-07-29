<?php

declare(strict_types=1);

namespace App\ResourceService;

use App\Api\Client\ClientFactory;
use App\Security\User;

readonly class UserService
{
    public function __construct(private ClientFactory $clientFactory)
    {
    }

    public function getByUsername(string $username): User
    {
        return $this->hydrateObject(
            $this->clientFactory->getAuthenticatedClient()->get("users?filter=username&value={$username}")
        );
    }

    public function getAuthenticatedUser(string $username, string $password): User
    {
        return $this->hydrateObject($this->clientFactory->getAnonymousClient()->authenticateUser($username, $password));
    }

    public function changeUserPassword(int $userId, string $username, string $oldPassword, string $newPassword): User
    {
        $this->getAuthenticatedUser($username, $oldPassword);
        $this->clientFactory->getAuthenticatedClient()->patch("users/{$userId}", payload: ['password' => $newPassword]);

        return $this->getAuthenticatedUser($username, $newPassword);
    }

    public function hydrateObject(array $data): User
    {
        return new User(
            intval($data['id']),
            strval($data['username']),
            strval($data['email']),
            (bool) $data['active'],
            isset($data['password']) ? strval($data['password']) : null,
            isset($data['token']) ? strval($data['token']) : null
        );
    }
}
