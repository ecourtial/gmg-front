<?php

declare(strict_types=1);

namespace App\ResourceService;

use App\Api\ResourceCollectionResponseDto;
use App\Entity\Dto\MagazineDto;

/**
 * @extends AbstractService<MagazineDto>
 */
class MagazineService extends AbstractService
{
    public function getList(): ResourceCollectionResponseDto
    {
        return $this->getCollection('orderBy[]=title-asc&limit='.self::MAX_RESULT_COUNT);
    }

    public function getByIds(array $magazinesIds): ResourceCollectionResponseDto
    {
        $query = 'id[]='.implode('&id[]=', $magazinesIds);

        return $this->getCollection($query.'&orderBy[]=year-asc&orderBy[]=month-asc&limit='.self::MAX_RESULT_COUNT);
    }

    protected function getResourceNamePlural(): string
    {
        return 'magazines';
    }

    protected function hydrateObject(array $data): MagazineDto
    {
        return new MagazineDto(
            $data['id'],
            $data['title'],
            $data['notes'],
            $data['issueCount'],
        );
    }
}
