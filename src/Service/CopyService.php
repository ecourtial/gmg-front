<?php

declare(strict_types=1);

namespace App\Service;

class CopyService extends AbstractService
{
    public const BOX_TYPES = [
        'None',
        'Big box',
        'Cartridge box',
        'Other',
    ];

    public const CASING_TYPES = [
        'DVD-like',
        'CD-like',
        'Cardboard sleeve',
        'Paper Sleeve',
        'Plastic Sleeve',
        'Plastic tube',
        'Other',
        'None',
    ];

    public const SUPPORT_TYPES = [
        'Blu-ray',
        'DVD-ROM',
        'CD-ROM',
        'GD-ROM',
        'MINI-Blu-ray',
        'MINI-DVD-ROM',
        'MINI-CD-ROM',
        'Cartridge',
        '3.5-inch floppy',
        '5.25-inch floppy',
        'Other disc',
        'Other floppy',
        'External drive',
        'None',
    ];

    public const TYPES = [
        'Physical',
        'Virtual',
    ];

    public const LANGUAGES = [
        'mul' => 'language_multi',
        'en' => 'language_english',
        'fr' => 'language_french',
        'es' => 'language_spanish',
        'ge' => 'language_german',
        'it' => 'language_italian',
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
