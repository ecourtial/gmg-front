<?php

declare(strict_types=1);

namespace App\ResourceService;

use App\Api\ResourceCollectionResponseDto;
use App\Entity\Dto\GameDto;
use App\Entity\Dto\Specific\GamesDataDto;
use App\Entity\Dto\Specific\VersionsDataDto;

/**
 * @extends AbstractService<GameDto>
 */
class GameService extends AbstractService
{
    public const string WITH_COMMENTS_FILTER = 'withComments';

    public const array FILTERS = [
        self::WITH_COMMENTS_FILTER => []
    ];

    public function getFirst(): ResourceCollectionResponseDto
    {
        return $this->hydrateResultCollection($this->clientFactory->getAnonymousClient()->get('games?page=1&limit=1'));
    }

    public function getList(): GamesDataDto
    {
        $data = $this->hydrateResultCollection(
            $this->clientFactory
            ->getAnonymousClient()
            ->get($this->getResourceType().'s?orderBy[]=title-asc&limit='.self::MAX_RESULT_COUNT)
        );

        $count = 0;
        foreach ($data->result as $game) {
            $count += intval(strval($game->versionCount));
        }

        return new GamesDataDto($data, $count);
    }

    public function search(string $keywords): VersionsDataDto
    {
        $data = $this->hydrateResultCollection(
            $this->clientFactory
                ->getAnonymousClient()
                ->get($this->getResourceType() . "s?title[]={$keywords}&orderBy[]=title-asc&page=1&limit=" . self::MAX_RESULT_COUNT)
        );

        $versionCount = 0;
        foreach ($data->result as $result) {
            $versionCount += $result->versionCount;
        }

        return new VersionsDataDto($data, $versionCount);
    }

    public function formatMentions(array $magazines, array $versions, array $mentions, array $issues): array
    {
        $mentionsData = [];

        foreach ($mentions as $mention) {
            $mentionType = $mention->type;

            if (false === array_key_exists($mentionType, $mentionsData)) {
                $mentionsData[$mentionType] = [];
            }

            $magazineIssueId = $mention->magazineIssueId;
            $issue = $issues[$magazineIssueId];
            $platformName = $versions[$mention->gameVersionId]->platformName;

            if (false === array_key_exists($platformName, $mentionsData[$mentionType])) {
                $mentionsData[$mentionType][$platformName] = [];
            }

            $mentionsData[$mentionType][$platformName][] = [
                'mentionId' => $mention->id,
                'magazineTitle' => $magazines[$issue->magazineId]->title,
                'magazineIssueId' => $magazineIssueId,
                'magazineIssueYear' => $issue->year,
                'magazineIssueMonth' => $issue->month,
                'magazineIssueNumber' => $issue->issueNumber,
                'pageNumber' => $mention->pageNumber,
                'notes' => $mention->notes,
            ];
        }

        return $mentionsData;
    }

    protected function getResourceType(): string
    {
        return 'game';
    }

    protected function hydrateObject(array $data): GameDto
    {
        return new GameDto(
            $data['id'],
            $data['title'],
            $data['notes'],
            $data['versionCount'],
        );
    }
}
