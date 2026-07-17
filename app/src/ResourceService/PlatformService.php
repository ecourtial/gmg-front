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
    public function getFirst(): ResourceCollectionResponseDto
    {
        return $this->hydrateResultCollection($this->clientFactory->getAnonymousClient()->get('platforms?page=1&limit=1'));
    }

    /**
     * @return ResourceCollectionResponseDto<PlatformDto>
     */
    public function getList(): ResourceCollectionResponseDto
    {
        return $this->hydrateResultCollection($this->clientFactory
            ->getAnonymousClient()
            ->get($this->getResourceType().'s?orderBy[]=name-asc&limit='.self::MAX_RESULT_COUNT)
        );
    }

    protected function getResourceType(): string
    {
        return 'platform';
    }

    protected function hydrateObject(array $data): PlatformDto
    {
        return new PlatformDto(
            $data['id'],
            $data['name'],
            $data['versionCount'],
        );
    }
}
