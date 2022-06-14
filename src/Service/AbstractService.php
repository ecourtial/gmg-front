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

    protected function removeEntry(string $resourceType, int $resourceId): void
    {
        $this->clientFactory->getReadWriteClient()->delete($resourceType . '/' . $resourceId);
    }
}
