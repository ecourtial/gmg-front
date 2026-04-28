<?php

declare(strict_types=1);

namespace App\Service;

use App\Security\User;

class UserService extends AbstractService
{
    protected function getResourceType(): string
    {
        return 'user';
    }

    public function getByUsername(string $username): User
    {
        $result = $this->clientFactory
            ->getAuthenticatedClient()
            ->get("user?filter=username&value={$username}");

        return new User($result['id'], $result['username'], $result['email'], $result['active']);
    }

    public function getAuthenticatedUser(string $username, string $password): User
    {
        $result = $this->clientFactory->getAnonymousClient()->authenticateUser($username, $password);

        return new User(
            $result['id'],
            $username,
            $result['email'],
            $result['active'],
            $password,
            $result['token']
        );
    }

    public function changePassword(int $userId, string $username, string $oldPassword, string $newPassword): User
    {
        $this->getAuthenticatedUser($username, $oldPassword);

        $this->clientFactory->getAuthenticatedClient()->patch(
            "user/{$userId}",
            [],
            ['password' => $newPassword]
        );

        return $this->getAuthenticatedUser($username, $newPassword);
    }
}
