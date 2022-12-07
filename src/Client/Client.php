<?php

declare(strict_types=1);

namespace App\Client;

use App\Exception\GenericApiException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class Client
{
    protected HttpClientInterface $client;

    public function __construct(private readonly string $backendUrl)
    {
        $this->client = HttpClient::create();
    }

    protected function execute(string $method, string $query, $headers = [], $payload = []): array
    {
        $headers = \array_merge(
            $headers,
            ['Content-Type' => 'application/json']
        );

        $targetUrl = $this->backendUrl . $query;

        try {
            return \json_decode(
                $this->client->request(
                    $method,
                    $targetUrl,
                    ['headers' => $headers, 'body' => \json_encode($payload)]
                )->getContent(),
                true
            );
        } catch (
            ClientExceptionInterface|TransportExceptionInterface $e
        ) {
            throw new GenericApiException($e, $targetUrl);
        }
    }
}
