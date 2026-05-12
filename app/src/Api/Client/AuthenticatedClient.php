<?php

declare(strict_types=1);

namespace App\Api\Client;

class AuthenticatedClient extends Client
{
    public function __construct(string $backendUrl, protected readonly string $userToken)
    {
        parent::__construct($backendUrl);
    }

    /**
     * @param array<string, string> $headers
     *
     * @return array<string, mixed>
     */
    public function get(string $query, array $headers = []): array
    {
        return $this->execute('GET', $query, $this->decorateHeaders($headers));
    }

    /**
     * @param array<string, string> $headers
     * @param array<string, mixed>  $payload
     *
     * @return array<string, mixed>
     */
    public function patch(string $query, array $headers = [], array $payload = []): array
    {
        return $this->execute('PATCH', $query, $this->decorateHeaders($headers), $payload);
    }

    /**
     * @param array<string, string> $headers
     * @param array<string, mixed>  $payload
     *
     * @return array<string, mixed>
     */
    public function delete(string $query, array $headers = [], array $payload = []): array
    {
        return $this->execute('DELETE', $query, $this->decorateHeaders($headers), $payload);
    }

    /**
     * @param array<string, string> $headers
     * @param array<string, mixed>  $payload
     *
     * @return array<string, mixed>
     */
    public function post(string $query, array $headers = [], array $payload = []): array
    {
        return $this->execute('POST', $query, $this->decorateHeaders($headers), $payload);
    }

    /**
     * @param array<string, string> $headers
     *
     * @return array<string, string>
     */
    private function decorateHeaders(array $headers): array
    {
        return \array_merge(
            $headers,
            ['Authorization' => 'token '.$this->userToken]
        );
    }
}
