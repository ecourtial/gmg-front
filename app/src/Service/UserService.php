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
        /** @var array<string, scalar> $result */
        $result = $this->clientFactory
            ->getAuthenticatedClient()
            ->get("user?filter=username&value={$username}");

        return new User(intval(strval($result['id'])), strval($result['username']), strval($result['email']), (bool) $result['active']);
    }

    public function getAuthenticatedUser(string $username, string $password): User
    {
        /** @var array<string, scalar> $result */
        $result = $this->clientFactory->getAnonymousClient()->authenticateUser($username, $password);

        return new User(
            intval(strval($result['id'])),
            $username,
            strval($result['email']),
            (bool) $result['active'],
            $password,
            isset($result['token']) ? strval($result['token']) : null
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
