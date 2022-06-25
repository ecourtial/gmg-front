<?php

declare(strict_types=1);

namespace App\Client;

class AuthenticatedClient extends Client
{
    public function __construct(string $backendUrl, protected readonly string $userToken)
    {
        parent::__construct($backendUrl);
    }

    public function get(string $query, array $headers = []): array
    {
        return $this->execute('GET', $query, $this->decorateHeaders($headers));
    }

    public function patch(string $query, array $headers = [], array $payload = []): array
    {
        return $this->execute('PATCH', $query, $this->decorateHeaders($headers), $payload);
    }

    public function delete(string $query, array $headers = [], array $payload = []): array
    {
        return $this->execute('DELETE', $query, $this->decorateHeaders($headers), $payload);
    }

    public function post(string $query, array $headers = [], array $payload = []): array
    {
        return $this->execute('POST', $query, $this->decorateHeaders($headers), $payload);
    }

    private function decorateHeaders(array $headers): array
    {
        return \array_merge(
            $headers,
            ['Authorization' => 'token ' . $this->userToken]
        );
    }
}
