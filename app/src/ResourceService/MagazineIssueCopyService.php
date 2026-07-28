<?php
declare(strict_types=1);

namespace App\ResourceService;

use App\Api\ResourceCollectionResponseDto;
use App\Entity\Dto\MagazineIssueCopyDto;

class MagazineIssueCopyService extends AbstractService
{
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
            $data['id'],
            $data['magazineIssueId'],
            $data['type'],
            $data['notes'],
        );
    }
}
