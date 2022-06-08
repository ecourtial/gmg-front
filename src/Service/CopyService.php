<?php

declare(strict_types=1);

namespace App\Service;

class CopyService extends AbstractService
{
    public function getByVersion(int $versionId): array
    {
        return $this->clientFactory
            ->getReadOnlyClient()
            ->get("copies?versionId[]={$versionId}&limit=" . self::MAX_RESULT_COUNT);
    }
}
