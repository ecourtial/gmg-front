<?php

declare(strict_types=1);

namespace App\ResourceService;

use App\Api\ResourceCollectionResponseDto;
use App\Entity\Dto\MagazineIssueDto;

/**
 * @extends AbstractService<MagazineIssueDto>
 */
class MagazineIssueService extends AbstractService
{
    public function getByMagazine(int $magazineId): ResourceCollectionResponseDto
    {
        return $this->getCollection("magazineId[]={$magazineId}&orderBy[]=year-asc&orderBy[]=month-asc&limit=".self::MAX_RESULT_COUNT);
    }

    public function getByIds(array $magazinesIds): ResourceCollectionResponseDto
    {
        $query = 'id[]='.implode('&id[]=', $magazinesIds);

        return $this->getCollection($query.'&orderBy[]=year-asc&orderBy[]=month-asc&limit='.self::MAX_RESULT_COUNT);
    }

    protected function getResourceNamePlural(): string
    {
        return 'magazine-issues';
    }

    protected function hydrateObject(array $data): MagazineIssueDto
    {
        return new MagazineIssueDto(
            $data['id'],
            $data['magazineId'],
            $data['issueNumber'],
            $data['year'],
            $data['month'],
            $data['notes'],
        );
    }
}
