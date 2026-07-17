<?php

declare(strict_types=1);

namespace App\ResourceService;

use App\Security\User;

/**
 * @extends AbstractService<User>
 */
class UserService extends AbstractService
{
    protected function getResourceType(): string
    {
        return 'user';
    }

    public function getByUsername(string $username): User
    {
        return $this->hydrateObject(
            $this->prepareDataFromResult(
                $this->clientFactory
                    ->getAuthenticatedClient()
                    ->get("user?filter=username&value={$username}")
            )
        );
    }

    public function getAuthenticatedUser(string $username, string $password): User
    {
        return $this->hydrateObject(
            $this->prepareDataFromResult(
                $this->clientFactory->getAnonymousClient()->authenticateUser($username, $password)
            )
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

    protected function hydrateObject(array $data): User
    {
        return new User(
            intval($data['id']),
            strval($data['username']),
            strval($data['email']),
            (bool) $data['active'],
            strval($data['password']),
            strval($data['token']),
        );
    }

    /**
     * @param array<string, scalar> $result
     * @return array<string, scalar>
     */
    private function prepareDataFromResult(array $result): array
    {
        return [
            'id' => $result['id'],
            'username' => $result['username'],
            'email' => $result['email'],
            'active' => $result['active'],
            'password' => isset($result['password']) ? strval($result['password']) : null,
            'token' => isset($result['token']) ? strval($result['token']) : null
        ];
    }
}
