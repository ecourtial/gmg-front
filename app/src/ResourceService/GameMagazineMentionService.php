<?php

declare(strict_types=1);

namespace App\ResourceService;

use App\Api\ResourceCollectionResponseDto;
use App\Entity\Dto\GameVersionMentionDto;

/**
 * @extends AbstractService<GameVersionMentionDto>
 */
class GameMagazineMentionService extends AbstractService
{
    public function getByIssueId(int $issueId): ResourceCollectionResponseDto
    {
        return $this->getCollection("magazineIssueId[]={$issueId}&orderBy[]=gameVersionId-asc&orderBy[]=pageNumber-asc&limit=".self::MAX_RESULT_COUNT);
    }

    public function getByVersionId(int $versionId): ResourceCollectionResponseDto
    {
        return $this->getCollection("gameVersionId[]={$versionId}&orderBy[]=pageNumber-asc&limit=".self::MAX_RESULT_COUNT);
    }

    public function getByVersionsIds(array $versionsIds): ResourceCollectionResponseDto
    {
        if (empty($versionsIds)) {
            return new ResourceCollectionResponseDto();
        }

        $versionsFilter = '';
        foreach ($versionsIds as $versionId) {
            $versionsFilter .= '&gameVersionId[]='.$versionId;
        }

        return $this->getCollection('orderBy[]=pageNumber-asc&limit='.self::MAX_RESULT_COUNT.$versionsFilter);
    }

    protected function getResourceNamePlural(): string
    {
        return 'game-version-magazine-mentions';
    }

    protected function hydrateObject(array $data): GameVersionMentionDto
    {
        return new GameVersionMentionDto(
            $data['id'],
            $data['magazineIssueId'],
            $data['gameVersionId'],
            $data['type'],
            $data['pageNumber'],
            $data['notes'],
        );
    }
}
