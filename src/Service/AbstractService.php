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

    public function delete(int $gameId): void
    {
        $this->clientFactory->getReadWriteClient()->delete('game/' . $gameId);
    }
}
