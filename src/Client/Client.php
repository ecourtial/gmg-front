<?php

declare(strict_types=1);

namespace App\Client;

use App\Exception\GenericApiException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Client
{
    private HttpClientInterface $client;

    public function __construct(private readonly string $backendUrl, private readonly string $readOnlyToken)
    {
        $this->client = HttpClient::create();
    }

    public function get(string $query, $headers = []): array
    {
        $headers = \array_merge(
            $headers,
            [
                'Authorization' => 'token ' . $this->readOnlyToken,
                'Content-Type' => 'application/json'
            ]
        );

        try {
            return \json_decode(
                $this->client->request(
                    'GET',
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