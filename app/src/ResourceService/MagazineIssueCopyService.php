<?php

declare(strict_types=1);

namespace App\ResourceService;

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

    protected function hydrateObject(array $data): MagazineIssueCopyDto
    {
        return new MagazineIssueCopyDto(
            (int) $data['id'],
            (int) $data['magazineIssueId'],
            (string) $data['type'],
            isset($data['notes']) ? (string) $data['notes'] : null,
        );
    }
}
