<?php

declare(strict_types=1);

namespace App\ResourceService;

use App\Api\ResourceCollectionResponseDto;
use App\Entity\Dto\PlatformDto;

/**
 * @extends AbstractService<PlatformDto>
 */
class PlatformService extends AbstractService
{
    /** @return ResourceCollectionResponseDto<PlatformDto> */
    public function getFirst(): ResourceCollectionResponseDto
    {
        return $this->getCollection('page=1&limit=1');
    }

    /**
     * @return ResourceCollectionResponseDto<PlatformDto>
     */
    public function getList(): ResourceCollectionResponseDto
    {
        return $this->getCollection('orderBy[]=name-asc&limit='.self::MAX_RESULT_COUNT);
    }

    protected function getResourceNamePlural(): string
    {
        return 'platforms';
    }

    protected function hydrateObject(array $data): PlatformDto
    {
        return new PlatformDto(
            (int) $data['id'],
            (string) $data['name'],
            (int) $data['versionCount'],
        );
    }
}
