<?php

declare(strict_types=1);

namespace App\PageService;

use App\Api\ResourceCollectionResponseDto;
use App\Entity\Dto\Specific\GameDetailsPageDto;
use App\Entity\Dto\Specific\GamesDataDto;
use App\Entity\Dto\Specific\GameVersionMentionDetailsDto;
use App\ResourceService\GameMagazineMentionService;
use App\ResourceService\GameService;
use App\ResourceService\VersionService;

readonly class GamePageService
{
    public function __construct(
        private GameService $gameService,
        private VersionService $versionService,
        private GameMagazineMentionService $gameMagazineMentionService,
        private GameVersionPageService $gameVersionPageService,
    ) {
    }

    public function getForDetailsPage(int $gameId): GameDetailsPageDto
    {
        $versionsData = $this->versionService->getByGame($gameId);

        $versions = [];
        foreach ($versionsData->versions->result as $version) {
            $versions[$version->id] = $version;
        }

        $game = $this->gameService->getById($gameId);

        $versionsIds = [];
        foreach ($versions as $version) {
            $versionsIds[] = $version->id;
        }

        // About the mentions
        $mentions = $this->gameMagazineMentionService->getByVersionsIds($versionsIds)->result;
        $mentionData = $this->gameVersionPageService->prepareMentions($mentions);
        $orderedMentions = $this->formatMentions($mentionData->magazines, $versions, $mentionData->mentions, $mentionData->issues);

        return new GameDetailsPageDto(
            $game,
            $versionsData,
            $orderedMentions
        );
    }

    public function getFilteredData(string $filter): GamesDataDto
    {
        // When using actual filtering, implement a method like we did in the VersionService.
        $data = $this->gameService->getList();

        /**
         * Ugly! @TODO implement that on API side please.
         * On top of that we could have used a simple getList() from the service.
         * But at least it reminds us that we need to improve filters on the API side.
         * On top of that, pagination is broken!
         */
        if (GameService::WITH_COMMENTS_FILTER === $filter) {
            $results = $data->games->result;
            $resultCount = 0;
            $totalResultCount = 0;
            $versionCount = 0;

            foreach ($results as $key => $item) {
                if (null === $item->notes
                    || '' === trim($item->notes)) {
                    unset($results[$key]);
                } else {
                    $versionCount += $item->versionCount;
                    ++$resultCount;
                    ++$totalResultCount;
                }
            }
            unset($item);

            return new GamesDataDto(
                new ResourceCollectionResponseDto(
                    $resultCount,
                    $totalResultCount,
                    result: $results,
                ),
                $versionCount
            );
        }

        throw new \LogicException("Unsupported game filter: '$filter'");
    }

    private function formatMentions(array $magazines, array $versions, array $mentions, array $issues): array
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

            $mentionsData[$mentionType][$platformName][] =
                    new GameVersionMentionDetailsDto(
                        $mention->id,
                        $magazines[$issue->magazineId]->title,
                        $magazineIssueId,
                        $issue->year,
                        $issue->month,
                        $issue->issueNumber,
                        $mention->pageNumber,
                        $mention->notes
                    );
        }

        return $mentionsData;
    }
}
