<?php

declare(strict_types=1);

namespace App\Client;

class AnonymousClient extends Client
{
    public function get(string $query, array $headers = []): array
    {
        return $this->execute('GET', $query, $headers);
    }

    public function authenticateUser(string $username, string $password): array
    {
        $customHeaders = ['Authorization' => 'Basic ' . \base64_encode("{$username}:{$password}")];

        return $this->execute('POST', 'user/authenticate', $customHeaders);
    }
}
