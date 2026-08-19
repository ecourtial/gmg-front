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

    /** @param array<string, mixed> $data */
    private function hydrateObject(array $data): User
    {
        $values = [];

        if (
            false === array_key_exists('id', $data)
            || false === is_integer($data['id'])
        ) {
            throw new \InvalidArgumentException('Impossible to hydrate the user object: id is missing.');
        }
        $values['id'] = (int) $data['id'];

        $stringKeys = ['username' => true, 'email' => true, 'password' => false, 'token' => false];
        foreach ($stringKeys as $key => $isMandatoryValue) {
            if (
                false === $isMandatoryValue
                && false === array_key_exists($key, $data)
            ) {
                continue;
            }

            if (false === is_string($data[$key])) {
                throw new \InvalidArgumentException('Impossible to hydrate the user object: some string values are missing.');
            }
            $values[$key] = $data[$key];
        }

        if (
            false === array_key_exists('active', $data)
            || false === is_bool($data['active'])
        ) {
            throw new \InvalidArgumentException('Impossible to hydrate the user object: the "active" key is missing.');
        }
        $values['active'] = $data['active'];

        return new User(
            $values['id'],
            $values['username'],
            $values['email'],
            $values['active'],
            isset($values['password']) ? (string) $values['password'] : null,
            isset($values['token']) ? (string) $values['token'] : null
        );
    }
}
