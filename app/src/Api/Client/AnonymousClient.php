<?php

declare(strict_types=1);

namespace App\Api\Client;

class AnonymousClient extends Client
{
    /**
     * @param array<string, string> $headers
     *
     * @return array<string, mixed>
     */
    public function get(string $query, array $headers = []): array
    {
        return $this->execute('GET', $query, $headers);
    }

    /** @return array<string, mixed> */
    public function authenticateUser(string $username, string $password): array
    {
        $customHeaders = ['Authorization' => 'Basic '.\base64_encode("{$username}:{$password}")];

        return $this->execute('POST', 'users/authenticate', $customHeaders);
    }
}
