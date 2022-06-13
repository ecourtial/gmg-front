<?php

declare(strict_types=1);

namespace App\Client;

use App\Exception\GenericApiException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ReadOnlyClient
{
    protected HttpClientInterface $client;

    public function __construct(private readonly string $backendUrl, private readonly string $readOnlyToken)
    {
        $this->client = HttpClient::create();
    }

    public function get(string $query, array $headers = []): array
    {
        return $this->execute('GET', $query, true, $headers);
    }

    public function authenticateUser(string $username, string $password): array
    {
        $customHeaders = ['Authorization' => 'Basic ' . \base64_encode("{$username}:{$password}")];

        return $this->execute('POST', 'user/authenticate', false, $customHeaders);
    }

    protected function execute(string $method, string $query, bool $auth = true, $headers = []): array
    {
        $headers = \array_merge(
            $headers,
            ['Content-Type' => 'application/json']
        );

        if ($auth) {
            $headers = \array_merge(
                $headers,
                ['Authorization' => 'token ' . $this->readOnlyToken]
            );
        }

        try {
            return \json_decode(
                $this->client->request(
                    $method,
                    $this->backendUrl . $query,
                    ['headers' => $headers]
                )->getContent(), true);
        } catch (
            ClientExceptionInterface|TransportExceptionInterface $e
        ) {
            throw new GenericApiException($e);
        }
    }
}
