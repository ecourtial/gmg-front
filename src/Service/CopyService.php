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

    public function getBigBoxesCopiesOnPC(): array
    {
        $result = $this->clientFactory
            ->getAnonymousClient()
            ->get("copies?orderBy[]=gameTitle-asc&platformName[]=PC&boxType[]=Big box&limit=" . self::MAX_RESULT_COUNT);

        // Remove duplicate
        $filteredResult = [];
        foreach ($result['result'] as $entry) {
            if (false === \array_key_exists($entry['versionId'], $filteredResult)) {
                $filteredResult[$entry['versionId']] = $entry;
            }
        }

        $result['result'] = $filteredResult;
        $result['totalResultCount'] = \count($filteredResult);

        return $result;
    }

    protected function getResourceType(): string
    {
        return 'copy';
    }
}
