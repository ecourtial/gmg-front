<?php

declare(strict_types=1);

namespace App\Client;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ReadWriteClient extends ReadOnlyClient
{
    protected HttpClientInterface $client;

    public function patch(string $query, array $headers = [], array $payload = []): array
    {
        return $this->execute('PATCH', $query, true, $headers, $payload);
    }
}
