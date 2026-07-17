<?php

declare(strict_types=1);

namespace App\ResourceService;

use App\Api\ResourceCollectionResponseDto;
use App\Entity\Dto\GameVersionDto;
use App\Entity\Dto\GameVersionMentionDto;
use App\Entity\Dto\MagazineDto;
use App\Entity\Dto\MagazineIssueDto;
use App\Entity\Dto\Specific\VersionsByPriorityDto;
use App\Entity\Dto\Specific\VersionsDataDto;

/**
 * @extends AbstractService<GameVersionDto>
 */
class VersionService extends AbstractService
{
    public const string WITH_COMMENTS_FILTER = 'withComments';

    public const array FILTERS = [
        'bgf' => [
            'attribute' => 'bestGameForever',
            'title' => 'best_game_forever_title',
            'description' => 'best_game_forever_description',
        ],
        'solo_recurring' => [
            'attribute' => 'singleplayerRecurring',
            'title' => 'single_player_recurring_title',
            'description' => 'single_player_recurring_description',
        ],
        'multi_recurring' => [
            'attribute' => 'multiplayerRecurring',
            'title' => 'multi_player_recurring_title',
            'description' => 'multi_player_recurring_description',
        ],
        'sometimes_solo' => [
            'attribute' => 'todoSoloSometimes',
            'title' => 'single_player_solo_title',
            'description' => 'single_player_sometimes_description',
        ],
        'multi_sometimes' => [
            'attribute' => 'todoMultiplayerSometimes',
            'title' => 'multi_player_sometimes_title',
            'description' => 'multi_player_sometimes_description',
        ],
        'on_going' => [
            'attribute' => 'ongoing',
            'title' => 'on_going_title',
            'description' => 'on_going_description',
        ],
        'to_buy' => [
            'attribute' => 'toBuy',
            'title' => 'to_buy_title',
            'description' => 'to_buy_description',
        ],
        'finished' => [
            'attribute' => 'finished',
            'title' => 'finished_title',
            'description' => 'finished_description',
        ],
        'not_finished' => [
            'attribute' => 'finished',
            'attribute_value' => 0,
            'title' => 'not_finished_title',
            'description' => 'not_finished_description',
        ],
        'originals' => [
            'attribute' => 'original',
            'title' => 'originals.title',
            'description' => 'originals.description',
            'filter_from_copies' => true,
        ],
        'physical' => [
            'attribute' => 'type',
            'title' => 'physical_title',
            'description' => 'physical_description',
            'filter_from_copies' => true,
            'attribute_value' => 'Physical',
        ],
        'virtual' => [
            'attribute' => 'type',
            'title' => 'virtual_title',
            'description' => 'virtual_description',
            'filter_from_copies' => true,
            'attribute_value' => 'Virtual',
        ],
        'bigBoxes' => [
            'attribute' => 'boxType',
            'title' => 'bigBoxes_title',
            'description' => 'bigBoxes_description',
            'filter_from_copies' => true,
            'attribute_value' => 'Big box&boxType[]=Medium box&boxType[]=Special box&reedition[]=0&onCompilation[]=0',
        ],
        // Ugly way to bypass API limitation. See the controller.
        self::WITH_COMMENTS_FILTER => [
            'attribute' => 'id',
            'title' => 'menu.version_with_comments',
            'description' => 'bigBoxes_description',
            'attribute_value' => 'neq-0',
        ],
    ];

    public const array FILTERS_WITH_PRIORITY = [
        'to_do' => [
            'attribute1' => 'toDo',
            'attribute2' => 'toDoPosition',
            'title' => 'to_do_title',
            'description' => 'to_do_description',
        ],
        'to_watch_in_background' => [
            'attribute1' => 'toWatchBackground',
            'attribute2' => 'toWatchPosition',
            'title' => 'to_watch_background_title',
            'description' => 'to_watch_background_description',
        ],
        'to_watch_serious' => [
            'attribute1' => 'toWatchSerious',
            'attribute2' => 'toWatchPosition',
            'title' => 'to_watch_serious_title',
            'description' => 'to_watch_serious_description',
        ],
    ];

    protected function hydrateObject(array $data): GameVersionDto
    {
        return new GameVersionDto(
            $data['id'],
            $data['platformId'],
            $data['gameId'],
            $data['releaseYear'],
            $data['todoSoloSometimes'],
            $data['todoMultiplayerSometimes'],
            $data['singleplayerRecurring'],
            $data['multiplayerRecurring'],
            $data['toDo'],
            $data['toBuy'],
            $data['toWatchBackground'],
            $data['toWatchSerious'],
            $data['toRewatch'],
            $data['topGame'],
            $data['hallOfFame'],
            $data['hallOfFameYear'],
            $data['hallOfFamePosition'],
            $data['playedItOften'],
            $data['ongoing'],
            $data['comments'],
            $data['todoWithHelp'],
            $data['bestGameForever'],
            $data['toWatchPosition'],
            $data['toDoPosition'],
            $data['finished'],
            $data['platformName'],
            $data['gameTitle'],
            $data['storyCount'],
            $data['copyCount'],
        );
    }

    public function getFirst(): ResourceCollectionResponseDto
    {
        return $this->hydrateResultCollection($this->clientFactory->getAnonymousClient()->get('versions?page=1&limit=1'));
    }

    public function getFinishedVersionsFirst(): ResourceCollectionResponseDto
    {
        return $this->hydrateResultCollection($this->clientFactory->getAnonymousClient()->get('versions?finished[]=1&page=1&limit=1'));
    }

    public function getOwnedGameFirst(): ResourceCollectionResponseDto
    {
        return $this->hydrateResultCollection($this->clientFactory->getAnonymousClient()->get('versions?copyCount[]=neq-0&limit=1'));
    }

    public function getTodoFirst(): ResourceCollectionResponseDto
    {
        return $this->hydrateResultCollection($this->clientFactory->getAnonymousClient()->get('versions?toDo[]=1&page=1&limit=1'));
    }

    public function getToWatchInBackgroundFirst(): ResourceCollectionResponseDto
    {
        return $this->hydrateResultCollection($this->clientFactory->getAnonymousClient()->get('versions?toWatchBackground[]=1&page=1&limit=1'));
    }

    public function getToWatchSeriousFirst(): ResourceCollectionResponseDto
    {
        return $this->hydrateResultCollection($this->clientFactory->getAnonymousClient()->get('versions?toWatchSerious[]=1&page=1&limit=1'));
    }

    public function getList(int $maxResultCount = self::MAX_RESULT_COUNT): VersionsDataDto
    {
        $versions = $this->hydrateResultCollection(
            $this->clientFactory
                ->getAnonymousClient()
                ->get($this->getResourceType().'s?orderBy[]=gameTitle-asc&page=1&limit='.$maxResultCount)
        );

        $count = 0;
        /** @var GameVersionDto $version */
        foreach ($versions->result as $version) {
            if ($version->copyCount > 0) {
                ++$count;
            }
        }

        return new VersionsDataDto($versions, $count);
    }

    public function getByPlatform(int $platformId, int $maxResultCount = self::MAX_RESULT_COUNT): VersionsDataDto
    {
        $versions = $this->hydrateResultCollection($this->clientFactory
            ->getAnonymousClient()
            ->get($this->getResourceType()."s?platformId[]={$platformId}&orderBy[]=gameTitle-asc&page=1&limit=".$maxResultCount)
        );

        $count = 0;
        /** @var GameVersionDto $version */
        foreach ($versions->result as $version) {
            if ($version->copyCount > 0) {
                ++$count;
            }
        }

        return new VersionsDataDto($versions, $count);
    }

    public function getByGame(int $gameId, int $maxResultCount = self::MAX_RESULT_COUNT): VersionsDataDto
    {
        /** @var array{result: list<array<string, scalar>>, totalResultCount: int} $versions */
        $versions = $this->hydrateResultCollection($this->clientFactory
            ->getAnonymousClient()
            ->get("versions?gameId[]={$gameId}&orderBy[]=gameTitle-asc&page=1&limit=".$maxResultCount)
        );

        $count = 0;
        foreach ($versions->result as $version) {
            if ($version->copyCount > 0) {
                ++$count;
            }
        }

        return new VersionsDataDto($versions, $count);
    }

    public function getFilteredList(
        string $filter,
        int $maxResultCount = self::MAX_RESULT_COUNT,
        ?ResourceCollectionResponseDto $copies = null,
    ): VersionsDataDto {
        $filterValue = strval(self::FILTERS[$filter]['attribute_value'] ?? '1');

        $filterAttribute = self::FILTERS[$filter]['attribute'];

        if (true === $copies instanceof ResourceCollectionResponseDto) {
            $data = $this->getListFromCopies($copies, $maxResultCount);
        } else {
            $data = $this->getListFromVersions($filterAttribute, $filterValue, $maxResultCount);
        }

        $count = 0;
        foreach ($data->result as $game) {
            if ($game->copyCount > 0) {
                ++$count;
            }
        }

        return new VersionsDataDto($data, $count);
    }

    public function getOriginals(ResourceCollectionResponseDto $copies): ResourceCollectionResponseDto
    {
        return $this->getFilteredList('originals', copies: $copies)->versions;
    }

    public function getHallOfFame(): ResourceCollectionResponseDto
    {
        return $this->hydrateResultCollection(
            $this
                ->clientFactory
                ->getAnonymousClient()
                ->get(
                    'versions?hallOfFame[]=1&hallOfFameYear[]=neq-0&hallOfFamePosition[]=neq-0'
                    .'&orderBy[]=hallOfFameYear-asc&orderBy[]=hallOfFamePosition-asc&limit='.self::MAX_RESULT_COUNT
                )
        );
    }

    public function getFilteredListWithPrio(string $filter, int $maxResultCount = self::MAX_RESULT_COUNT): VersionsByPriorityDto
    {
        $filter1 = self::FILTERS_WITH_PRIORITY[$filter]['attribute1'];
        $filter2 = self::FILTERS_WITH_PRIORITY[$filter]['attribute2'];

        $result = $this->hydrateResultCollection(
            $this->clientFactory
            ->getAnonymousClient()
            ->get(
                "versions?{$filter1}[]=1&orderBy[]={$filter2}-asc&orderBy[]=gameTitle-asc&page=1&limit=".$maxResultCount
            )
        );

        $orderedResult = [
            'withPriority' => [],
            'withoutPriority' => [],
        ];

        foreach ($result->result as $version) {
            if ($version->$filter2 > 0) {
                $orderedResult['withPriority'][] = $version;
            } else {
                $orderedResult['withoutPriority'][] = $version;
            }
        }

        $count = 0;
        foreach ($orderedResult as $subset) {
            foreach ($subset as $game) {
                if ($game->copyCount > 0) {
                    ++$count;
                }
            }
        }
        $orderedResult['ownedCount'] = $count;
        $orderedResult['totalResultCount'] = $result->totalResultCount;

        // Now order by range
        $min = -9;
        $max = 0;
        $range = "$min-$max";
        $versions = [];
        foreach ($orderedResult['withPriority'] as $value) {
            if ($value->toDoPosition > $max) {
                $min = $max + 1;
                $max = $min + 9;
                $range = "$min-$max";
                $versions[$range] = [];
            }
            $versions[$range][] = $value;
        }
        $orderedResult['withPriority'] = $versions;
        // End order by range

        return new VersionsByPriorityDto(
            $orderedResult['withPriority'],
            $orderedResult['withoutPriority'],
            $orderedResult['ownedCount'],
            $orderedResult['totalResultCount'],
        );
    }

    public function getRandom(string $filter): ResourceCollectionResponseDto
    {
        $soloFilters = ['todoSoloSometimes', 'singleplayerRecurring', 'toDo'];
        $multiFilters = ['todoMultiplayerSometimes', 'multiplayerRecurring'];

        if ('singleplayer_random' === $filter) {
            $filter = $soloFilters[array_rand($soloFilters)];
        } elseif ('multiplayer_random' === $filter) {
            $filter = $multiFilters[array_rand($multiFilters)];
        } elseif (false === \in_array($filter, $soloFilters)
            && false === \in_array($filter, $multiFilters)
        ) {
            return new ResourceCollectionResponseDto();
        }

        return $this->hydrateResultCollection($this->clientFactory
            ->getAnonymousClient()
            ->get("versions?{$filter}[]=1'&orderBy[]=rand&page=1&limit=1")
        );
    }

    public function search(string $keywords, int $maxResultCount = self::MAX_RESULT_COUNT): VersionsDataDto
    {
        $data = $this->hydrateResultCollection(
            $this->clientFactory
            ->getAnonymousClient()
            ->get("versions?gameTitle[]={$keywords}&orderBy[]=gameTitle-asc&page=1&limit=".$maxResultCount)
        );

        $count = 0;
        foreach ($data->result as $game) {
            if ($game->copyCount > 0) {
                ++$count;
            }
        }


        return new VersionsDataDto($data, $count);
    }

    public function getOriginalsWhereCopyIsNotOnCompilation(ResourceCollectionResponseDto $copies): ResourceCollectionResponseDto
    {
        return $this->getListFromCopies($copies);
    }

    protected function getResourceType(): string
    {
        return 'version';
    }

    protected function getListFromVersions(string $filter, string $filterValue, int $maxResultCount = self::MAX_RESULT_COUNT): ResourceCollectionResponseDto
    {
        return $this->hydrateResultCollection(
            $this->clientFactory
            ->getAnonymousClient()
            ->get("versions?{$filter}[]={$filterValue}&orderBy[]=gameTitle-asc&page=1&limit=".$maxResultCount)
        );
    }

    protected function getListFromCopies(
        ResourceCollectionResponseDto $copies,
        int $maxResultCount = self::MAX_RESULT_COUNT,
    ): ResourceCollectionResponseDto {
        $versionIds = [];

        foreach ($copies->result as $copy) {
            if (true === $copy->original && false === \in_array($copy->versionId, $versionIds)) {
                $versionIds[] = $copy->versionId;
            }
        }

        if ([] === $versionIds) {
            return new ResourceCollectionResponseDto();
        }

        $query = '';
        foreach ($versionIds as $id) {
            $query .= '&id[]='.$id;
        }

        return $this->hydrateResultCollection(
            $this->clientFactory
            ->getAnonymousClient()
            ->get("versions?orderBy[]=gameTitle-asc{$query}&page=1&limit=".$maxResultCount)
        );
    }

    /**
     * @param MagazineDto[] $magazines
     * @param MagazineIssueDto[] $issues
     * @param GameVersionMentionDto[] $mentions
     */
    public function formatMentions(array $magazines, array $issues, array $mentions): array
    {
        $mentionsData = [];

        foreach ($mentions as $mention) {
            $mentionType = $mention->type;

            if (false === array_key_exists($mentionType, $mentionsData)) {
                $mentionsData[$mentionType] = [];
            }

            $magazineIssueId = $mention->magazineIssueId;
            $issue = $issues[$magazineIssueId];

            $mentionsData[$mentionType][] = [
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
}
