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

    /**
     * @param array<string, string> $headers
     * @param array<string, mixed>  $payload
     *
     * @return array<string, mixed>
     */
    protected function execute(string $method, string $query, array $headers = [], array $payload = []): array
    {
        $headers = \array_merge(
            $headers,
            ['Content-Type' => 'application/json', 'Accept' => 'application/json', 'User-Agent' => 'GMG-Front/2.0']
        );

        $targetUrl = $this->backendUrl.$query;

        try {
            /** @var array<string, mixed> $result */
            $result = (array) \json_decode(
                $this->client->request(
                    $method,
                    $targetUrl,
                    [
                        'headers' => $headers, 'body' => \json_encode($payload),
                        'timeout' => 5, // According to the doc: "...the maximum total duration of the request, including DNS, connect, TLS, redirects, and reading the response body."
                        'max_duration' => 5,
                        'max_redirects' => 1,
                    ]
                )->getContent(),
                true
            );
        } catch (
            ClientExceptionInterface|TransportExceptionInterface $e
        ) {
            throw new GenericApiException($e, $targetUrl);
        }

        return $result;
    }
}
