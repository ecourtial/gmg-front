<?php
declare(strict_types=1);

namespace App\Service;

class GameMagazineMentionService extends AbstractService
{
    /** @return array<string, mixed> */
    public function getByIssueId(int $issueId): array
    {
        /** @var array{result: list<array<string, scalar>>, totalResultCount: int} $data */
        $data = $this->clientFactory
            ->getAnonymousClient()
            ->get("game-version-magazine-mentions?magazineIssueId[]={$issueId}&orderBy[]=gameVersionId-asc&orderBy[]=pageNumber-asc&limit=".self::MAX_RESULT_COUNT);

        return $data;
    }

    /** @return array<string, mixed> */
    public function getByVersionId(int $versionId): array
    {
        /** @var array{result: list<array<string, scalar>>, totalResultCount: int} $data */
        $data = $this->clientFactory
            ->getAnonymousClient()
            ->get("game-version-magazine-mentions?gameVersionId[]={$versionId}&orderBy[]=pageNumber-asc&limit=".self::MAX_RESULT_COUNT);

        return $data;
    }

    /** @return array<string, mixed> */
    public function getByVersionsIds(array $versionsIds): array
    {
        if (empty($versionsIds)) return ['result' => []];

        $versionsFilter = '';
        foreach ($versionsIds as $versionId) {
            $versionsFilter .= "&gameVersionId[]=".$versionId;
        }

        /** @var array{result: list<array<string, scalar>>, totalResultCount: int} $data */
        $data = $this->clientFactory
            ->getAnonymousClient()
            ->get("game-version-magazine-mentions?orderBy[]=pageNumber-asc&limit=".self::MAX_RESULT_COUNT.$versionsFilter);

        return $data;
    }

    protected function getResourceType(): string
    {
        return 'game-version-magazine-mention';
    }
}
