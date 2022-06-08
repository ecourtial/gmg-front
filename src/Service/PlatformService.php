<?php

declare(strict_types=1);

namespace App\Service;

class PlatformService extends AbstractService
{
    public function getList(): array
    {
        return $this->clientFactory
            ->getReadOnlyClient()
            ->get('platforms?orderBy[]=name-asc&limit=' . self::MAX_RESULT_COUNT);
    }
}
