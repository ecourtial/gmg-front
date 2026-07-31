<?php

declare(strict_types=1);

namespace App\ResourceService;

use App\Api\ResourceCollectionResponseDto;
use App\Api\RawSingleResourceApiResponseDto;
use App\Entity\Dto\GameVersionCopyDto;

/**
 * @extends AbstractService<GameVersionCopyDto>
 */
class CopyService extends AbstractService
{
    public const array BOX_TYPES = [
        'None',
        'Big box',
        'Medium box',
        'Special box',
        'Cartridge box',
        'Other',
    ];

    public const array CASING_TYPES = [
        'DVD-like',
        'CD-like',
        'Cardboard sleeve',
        'Paper Sleeve',
        'Plastic Sleeve',
        'Plastic tube',
        'Other',
        'None',
    ];

    public const array SUPPORT_TYPES = [
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

    public const array TYPES = [
        'Physical',
        'Virtual',
    ];

    public const array REGIONS = [
        'PAL',
        'JAP',
        'NTSC',
        'CHINA',
    ];

    public const array LANGUAGES = [
        'mul' => 'language_multi',
        'en' => 'language_english',
        'fr' => 'language_french',
        'es' => 'language_spanish',
        'ge' => 'language_german',
        'it' => 'language_italian',
    ];

    /**
     * @return ResourceCollectionResponseDto<GameVersionCopyDto>
     */
    public function getByVersion(int $versionId): ResourceCollectionResponseDto
    {
        return $this->getCollection("versionId[]={$versionId}&limit=".self::MAX_RESULT_COUNT);
    }

    /**
     * @return ResourceCollectionResponseDto<GameVersionCopyDto>
     */
    public function getList(
        string $filter,
        string $filterValue,
        int $maxResultCount = self::MAX_RESULT_COUNT,
    ): ResourceCollectionResponseDto {
        // There is a limit of the API here... Consider allowing more accurate filtering
        return $this->getCollection("{$filter}[]={$filterValue}&orderBy[]=gameTitle-asc&limit=".$maxResultCount);
    }

    /**
     * @return ResourceCollectionResponseDto<GameVersionCopyDto>
     */
    public function getOriginals(): ResourceCollectionResponseDto
    {
        return $this->getList(
            'original',
            '1'
        );
    }

    /**
     * @return ResourceCollectionResponseDto<GameVersionCopyDto>
     */
    public function getOriginalsWhereCopyIsNotOnCompilation(): ResourceCollectionResponseDto
    {
        return $this->getList(
            'onCompilation[]=0&original',
            '1'
        );
    }

    protected function getResourceNamePlural(): string
    {
        return 'copies';
    }

    protected function hydrateObject(RawSingleResourceApiResponseDto $dto): GameVersionCopyDto
    {
        return new GameVersionCopyDto(
            (int) $dto->data['id'],
            (int) $dto->data['versionId'],
            (bool) $dto->data['original'],
            (string) $dto->data['language'],
            (string) $dto->data['boxType'],
            (bool) $dto->data['isBoxRepro'],
            (string) $dto->data['casingType'],
            (string) $dto->data['supportType'],
            (bool) $dto->data['onCompilation'],
            (bool) $dto->data['reedition'],
            (bool) $dto->data['hasManual'],
            (string) $dto->data['status'],
            (string) $dto->data['type'],
            (string) $dto->data['region'],
            isset($dto->data['comments']) ? (string) $dto->data['comments'] : null,
            (bool) $dto->data['isROM'],
            (string) $dto->data['platformName'],
            (string) $dto->data['gameTitle'],
            (int) $dto->data['transactionCount'],
        );
    }
}
