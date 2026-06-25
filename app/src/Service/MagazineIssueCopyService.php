<?php
declare(strict_types=1);

namespace App\Service;

class MagazineIssueCopyService extends AbstractService
{
    /** @return array<string, mixed> */
    public function getByIssueId(int $issueId): array
    {
        /** @var array{result: list<array<string, scalar>>, totalResultCount: int} $data */
        $data = $this->clientFactory
            ->getAnonymousClient()
            ->get("magazine-issue-copies?magazineIssueId[]={$issueId}&orderBy[]=magazineIssueId-asc&limit=".self::MAX_RESULT_COUNT);

        return $data;
    }

    protected function getResourceType(): string
    {
        return 'magazine-issue-copy';
    }
}
