<?php

declare(strict_types=1);

namespace App\Service;

class CopyService extends AbstractService
{
    public const BOX_TYPES = [
        'Big box',
        'DVD',
        'CD',
        'None',
    ];

    public const CASING_TYPES = [
        'DVD',
        'CD',
        'Cardboard sleeve',
        'Paper Sleeve',
        'Plastic Sleeve',
        'None',
    ];

    public const TYPES = [
        'Physical',
        'Virtual',
    ];

    public function getByVersion(int $versionId): array
    {
        return $this->clientFactory
            ->getAnonymousClient()
            ->get("copies?versionId[]={$versionId}&limit=" . self::MAX_RESULT_COUNT);
    }

    protected function getResourceType(): string
    {
        return 'copy';
    }
}
