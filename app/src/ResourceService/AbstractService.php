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

    public function __construct(protected readonly ClientFactory $clientFactory)
    {
    }

    abstract protected function getResourceType(): string;

    /**
     * @param array<string, mixed> $data
     * @return TDto
     */
    abstract protected function hydrateObject(array $data): object;

    /**
     * @param array<string, mixed> $data
     *
     * @return ResourceCollectionResponseDto<TDto>
     */
    protected function hydrateResultCollection(array $data): ResourceCollectionResponseDto
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

    /**
     * @return TDto
     */
    public function getById(int $entityId): object
    {
        return $this->hydrateObject(
            $this->clientFactory
                ->getAnonymousClient()
                ->get("{$this->getResourceType()}/{$entityId}")
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
                $this->getResourceType(),
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
                "{$this->getResourceType()}/{$entityId}",
                [],
                $data
            )
        );
    }

    public function delete(int $entityId): void
    {
        $this->clientFactory->getAuthenticatedClient()->delete($this->getResourceType().'/'.$entityId);
    }
}
