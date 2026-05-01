<?php

declare(strict_types=1);

namespace App\Service;

use App\Client\ClientFactory;

abstract class AbstractService
{
    protected const MAX_RESULT_COUNT = 500;

    public function __construct(protected readonly ClientFactory $clientFactory)
    {
    }

    abstract protected function getResourceType(): string;

    /** @return array<string, mixed> */
    public function getById(int $entityId): array
    {
        return $this->clientFactory
            ->getAnonymousClient()
            ->get("{$this->getResourceType()}/{$entityId}");
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function add(array $data): array
    {
        return $this->clientFactory->getAuthenticatedClient()->post(
            $this->getResourceType(),
            [],
            $data
        );
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function update(int $entityId, array $data): array
    {
        return $this->clientFactory->getAuthenticatedClient()->patch(
            "{$this->getResourceType()}/{$entityId}",
            [],
            $data
        );
    }

    public function delete(int $entityId): void
    {
        $this->clientFactory->getAuthenticatedClient()->delete($this->getResourceType().'/'.$entityId);
    }
}
