<?php

declare(strict_types=1);

namespace App\ResourceService;

use App\Api\ResourceCollectionResponseDto;
use App\Api\RawSingleResourceApiResponseDto;
use App\Entity\Dto\MagazineIssueDto;

/**
 * @extends AbstractService<MagazineIssueDto>
 */
class MagazineIssueService extends AbstractService
{
    /**
     * @return ResourceCollectionResponseDto<MagazineIssueDto>
     */
    public function getByMagazine(int $magazineId): ResourceCollectionResponseDto
    {
        return $this->getCollection("magazineId[]={$magazineId}&orderBy[]=year-asc&orderBy[]=month-asc&limit=".self::MAX_RESULT_COUNT);
    }

    /**
     * @param int[] $magazinesIds
     * @return ResourceCollectionResponseDto<MagazineIssueDto>
     */
    public function getByIds(array $magazinesIds): ResourceCollectionResponseDto
    {
        $query = 'id[]='.implode('&id[]=', $magazinesIds);

        return $this->getCollection($query.'&orderBy[]=year-asc&orderBy[]=month-asc&limit='.self::MAX_RESULT_COUNT);
    }

    protected function getResourceNamePlural(): string
    {
        return 'magazine-issues';
    }

    protected function hydrateObject(RawSingleResourceApiResponseDto $dto): MagazineIssueDto
    {
        return new MagazineIssueDto(
            (int) $dto->data['id'],
            (int) $dto->data['magazineId'],
            (int) $dto->data['issueNumber'],
            (int) $dto->data['year'],
            (int) $dto->data['month'],
            isset($dto->data['notes']) ? (string) $dto->data['notes'] : null,
        );
    }
}
