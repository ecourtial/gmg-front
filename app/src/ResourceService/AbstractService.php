<?php

declare(strict_types=1);

namespace App\ResourceService;

use App\Api\Client\ClientFactory;
use App\Api\ResourceCollectionResponseDto;

/**
 * @template TDto of object
 */
abstract class AbstractService
{
    protected const int MAX_RESULT_COUNT = 1000;

    public function __construct(private readonly ClientFactory $clientFactory) {}

    /**
     * @return TDto
     */
    public function getById(int $entityId): object
    {
        return $this->hydrateObject(
            $this->clientFactory
                ->getAnonymousClient()
                ->get("{$this->getResourceNamePlural()}/{$entityId}")
        );
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return TDto
     */
    public function add(array $data): object
    {
        return $this->hydrateObject(
            $this->clientFactory->getAuthenticatedClient()->post(
                $this->getResourceNamePlural(),
                [],
                $data
            )
        );
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return TDto
     */
    public function update(int $entityId, array $data): object
    {
        return $this->hydrateObject(
            $this->clientFactory->getAuthenticatedClient()->patch(
                "{$this->getResourceNamePlural()}/{$entityId}",
                [],
                $data
            )
        );
    }

    public function delete(int $entityId): void
    {
        $this->clientFactory->getAuthenticatedClient()->delete($this->getResourceNamePlural().'/'.$entityId);
    }

    /**
     * @return ResourceCollectionResponseDto<TDto>
     */
    protected function getCollection(string $query, bool $authenticated = false): ResourceCollectionResponseDto
    {
        if (true === $authenticated) {
            $client = $this->clientFactory->getAuthenticatedClient();
        } else {
            $client = $this->clientFactory->getAnonymousClient();
        }

        return $this->hydrateResultCollection(
            $client->get($this->getResourceNamePlural().'?'.$query)
        );
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return ResourceCollectionResponseDto<TDto>
     */
    private function hydrateResultCollection(array $data): ResourceCollectionResponseDto
    {
        $resources = [];

        foreach ($data['result'] as $item) {
            $resources[] = $this->hydrateObject($item);
        }

        return new ResourceCollectionResponseDto(
            $data['resultCount'],
            $data['totalResultCount'],
            $data['page'],
            $data['totalPageCount'],
            $resources,
        );
    }

    abstract protected function getResourceNamePlural(): string;

    /**
     * @param array<string, mixed> $data
     * @return TDto
     */
    abstract protected function hydrateObject(array $data): object;
}
