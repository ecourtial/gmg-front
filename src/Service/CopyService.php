<?php

declare(strict_types=1);

namespace App\Service;

class CopyService extends AbstractService
{
    public const BOX_TYPES = [
        'None',
        'Big box',
        'Medium box',
        'Special box',
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

    public const REGIONS = [
        'PAL',
        'JAP',
        'NTSC',
        'CHINA',
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

    protected function getResourceType(): string
    {
        return 'copy';
    }
}
