<?php

declare(strict_types=1);

namespace App\ResourceService;

use App\Api\ResourceCollectionResponseDto;
use App\Api\RawSingleResourceApiResponseDto;
use App\Entity\Dto\GameVersionMentionDto;

/**
 * @extends AbstractService<GameVersionMentionDto>
 */
class GameMagazineMentionService extends AbstractService
{
    /**
     * @return ResourceCollectionResponseDto<GameVersionMentionDto>
     */
    public function getByIssueId(int $issueId): ResourceCollectionResponseDto
    {
        return $this->getCollection("magazineIssueId[]={$issueId}&orderBy[]=gameVersionId-asc&orderBy[]=pageNumber-asc&limit=".self::MAX_RESULT_COUNT);
    }

    /**
     * @return ResourceCollectionResponseDto<GameVersionMentionDto>
     */
    public function getByVersionId(int $versionId): ResourceCollectionResponseDto
    {
        return $this->getCollection("gameVersionId[]={$versionId}&orderBy[]=pageNumber-asc&limit=".self::MAX_RESULT_COUNT);
    }

    /**
     * @param int[] $versionsIds
     * @return ResourceCollectionResponseDto<GameVersionMentionDto>
     */
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

    protected function hydrateObject(RawSingleResourceApiResponseDto $dto): GameVersionMentionDto
    {
        return new GameVersionMentionDto(
            (int) $dto->data['id'],
            (int) $dto->data['magazineIssueId'],
            (int) $dto->data['gameVersionId'],
            (string) $dto->data['type'],
            (int) $dto->data['pageNumber'],
            isset($dto->data['notes']) ? (string) $dto->data['notes'] : null,
        );
    }
}
