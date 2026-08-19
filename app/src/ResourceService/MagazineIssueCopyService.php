<?php

declare(strict_types=1);

namespace App\ResourceService;

use App\Api\RawSingleResourceApiResponseDto;
use App\Api\ResourceCollectionResponseDto;
use App\Entity\Dto\MagazineIssueCopyDto;

/**
 * @extends AbstractService<MagazineIssueCopyDto>
 */
class MagazineIssueCopyService extends AbstractService
{
    /**
     * @return ResourceCollectionResponseDto<MagazineIssueCopyDto>
     */
    public function getByIssueId(int $issueId): ResourceCollectionResponseDto
    {
        return $this->getCollection("magazineIssueId[]={$issueId}&orderBy[]=magazineIssueId-asc&limit=".self::MAX_RESULT_COUNT);
    }

    protected function getResourceNamePlural(): string
    {
        return 'magazine-issue-copies';
    }

    protected function hydrateObject(RawSingleResourceApiResponseDto $dto): MagazineIssueCopyDto
    {
        return new MagazineIssueCopyDto(
            (int) $dto->data['id'],
            (int) $dto->data['magazineIssueId'],
            (string) $dto->data['type'],
            isset($dto->data['notes']) ? (string) $dto->data['notes'] : null,
        );
    }
}
