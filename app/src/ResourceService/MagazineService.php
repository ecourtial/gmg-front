<?php

declare(strict_types=1);

namespace App\ResourceService;

use App\Api\RawSingleResourceApiResponseDto;
use App\Api\ResourceCollectionResponseDto;
use App\Entity\Dto\MagazineDto;

/**
 * @extends AbstractService<MagazineDto>
 */
class MagazineService extends AbstractService
{
    /**
     * @return ResourceCollectionResponseDto<MagazineDto>
     */
    public function getList(): ResourceCollectionResponseDto
    {
        return $this->getCollection('orderBy[]=title-asc&limit='.self::MAX_RESULT_COUNT);
    }

    /**
     * @param int[] $magazinesIds
     *
     * @return ResourceCollectionResponseDto<MagazineDto>
     */
    public function getByIds(array $magazinesIds): ResourceCollectionResponseDto
    {
        $query = 'id[]='.implode('&id[]=', $magazinesIds);

        return $this->getCollection($query.'&orderBy[]=year-asc&orderBy[]=month-asc&limit='.self::MAX_RESULT_COUNT);
    }

    protected function getResourceNamePlural(): string
    {
        return 'magazines';
    }

    protected function hydrateObject(RawSingleResourceApiResponseDto $dto): MagazineDto
    {
        return new MagazineDto(
            (int) $dto->data['id'],
            (string) $dto->data['title'],
            (int) $dto->data['issueCount'],
            isset($dto->data['notes']) ? (string) $dto->data['notes'] : null,
        );
    }
}
