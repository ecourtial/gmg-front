<?php

declare(strict_types=1);

namespace App\ResourceService;

use App\Api\ResourceCollectionResponseDto;
use App\Api\RawSingleResourceApiResponseDto;
use App\Entity\Dto\GameVersionCopyDto;
use App\Entity\Dto\GameVersionDto;
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

    /**
     * @param int[] $versionsIds
     * @return ResourceCollectionResponseDto<GameVersionDto>
     */
    public function getByIds(array $versionsIds): ResourceCollectionResponseDto
    {
        if (empty($versionsIds)) {
            return new ResourceCollectionResponseDto();
        }

        $versionsFilter = '';
        foreach ($versionsIds as $versionId) {
            $versionsFilter .= '&id[]='.$versionId;
        }

        return $this->getCollection('orderBy[]=pageNumber-asc&limit='.self::MAX_RESULT_COUNT.$versionsFilter);
    }

    /**
     * @return ResourceCollectionResponseDto<GameVersionDto>
     */
    public function getFirst(): ResourceCollectionResponseDto
    {
        return $this->getCollection('page=1&limit=1');
    }

    /**
     * @return ResourceCollectionResponseDto<GameVersionDto>
     */
    public function getFinishedVersionsFirst(): ResourceCollectionResponseDto
    {
        return $this->getCollection('finished[]=1&page=1&limit=1');
    }

    /**
     * @return ResourceCollectionResponseDto<GameVersionDto>
     */
    public function getOwnedGameFirst(): ResourceCollectionResponseDto
    {
        return $this->getCollection('copyCount[]=neq-0&limit=1');
    }

    /**
     * @return ResourceCollectionResponseDto<GameVersionDto>
     */
    public function getTodoFirst(): ResourceCollectionResponseDto
    {
        return $this->getCollection('toDo[]=1&page=1&limit=1');
    }

    /**
     * @return ResourceCollectionResponseDto<GameVersionDto>
     */
    public function getToWatchInBackgroundFirst(): ResourceCollectionResponseDto
    {
        return $this->getCollection('toWatchBackground[]=1&page=1&limit=1');
    }

    /**
     * @return ResourceCollectionResponseDto<GameVersionDto>
     */
    public function getToWatchSeriousFirst(): ResourceCollectionResponseDto
    {
        return $this->getCollection('toWatchSerious[]=1&page=1&limit=1');
    }

    public function getList(int $maxResultCount = self::MAX_RESULT_COUNT): VersionsDataDto
    {
        $versions = $this->getCollection('orderBy[]=gameTitle-asc&page=1&limit='.$maxResultCount);

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
        $versions = $this->getCollection("platformId[]={$platformId}&orderBy[]=gameTitle-asc&page=1&limit=".$maxResultCount);

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
        $versions = $this->getCollection("gameId[]={$gameId}&orderBy[]=gameTitle-asc&page=1&limit=".$maxResultCount);

        $count = 0;
        foreach ($versions->result as $version) {
            if ($version->copyCount > 0) {
                ++$count;
            }
        }

        return new VersionsDataDto($versions, $count);
    }

    /**
     * @param ResourceCollectionResponseDto<GameVersionCopyDto>|null $copies
     */
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

    /**
     * @param ResourceCollectionResponseDto<GameVersionCopyDto> $copies
     * @return ResourceCollectionResponseDto<GameVersionDto>
     */
    public function getOriginals(ResourceCollectionResponseDto $copies): ResourceCollectionResponseDto
    {
        return $this->getFilteredList('originals', copies: $copies)->versions;
    }

    /**
     * @return ResourceCollectionResponseDto<GameVersionDto>
     */
    public function getHallOfFame(): ResourceCollectionResponseDto
    {
        return $this->getCollection('hallOfFame[]=1&hallOfFameYear[]=neq-0&hallOfFamePosition[]=neq-0'
                    .'&orderBy[]=hallOfFameYear-asc&orderBy[]=hallOfFamePosition-asc&limit='.self::MAX_RESULT_COUNT);
    }

    public function getFilteredListWithPrio(string $filter, int $maxResultCount = self::MAX_RESULT_COUNT): VersionsByPriorityDto
    {
        $filter1 = self::FILTERS_WITH_PRIORITY[$filter]['attribute1'];
        $filter2 = self::FILTERS_WITH_PRIORITY[$filter]['attribute2'];

        $result = $this->getCollection("{$filter1}[]=1&orderBy[]={$filter2}-asc&orderBy[]=gameTitle-asc&page=1&limit=".$maxResultCount);

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

    /**
     * @return ResourceCollectionResponseDto<GameVersionDto>
     */
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

        return $this->getCollection("{$filter}[]=1'&orderBy[]=rand&page=1&limit=1");
    }

    public function search(string $keywords, int $maxResultCount = self::MAX_RESULT_COUNT): VersionsDataDto
    {
        $data = $this->getCollection("gameTitle[]={$keywords}&orderBy[]=gameTitle-asc&page=1&limit=".$maxResultCount);

        $count = 0;
        foreach ($data->result as $game) {
            if ($game->copyCount > 0) {
                ++$count;
            }
        }

        return new VersionsDataDto($data, $count);
    }

    /**
     * @param ResourceCollectionResponseDto<GameVersionCopyDto> $copies
     * @return ResourceCollectionResponseDto<GameVersionDto>
     */
    public function getOriginalsWhereCopyIsNotOnCompilation(ResourceCollectionResponseDto $copies): ResourceCollectionResponseDto
    {
        return $this->getListFromCopies($copies);
    }

    protected function getResourceNamePlural(): string
    {
        return 'versions';
    }

    /**
     * @return ResourceCollectionResponseDto<GameVersionDto>
     */
    protected function getListFromVersions(string $filter, string $filterValue, int $maxResultCount = self::MAX_RESULT_COUNT): ResourceCollectionResponseDto
    {
        return $this->getCollection("{$filter}[]={$filterValue}&orderBy[]=gameTitle-asc&page=1&limit=".$maxResultCount);
    }

    /**
     * @param ResourceCollectionResponseDto<GameVersionCopyDto> $copies
     * @return ResourceCollectionResponseDto<GameVersionDto>
     */
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

        return $this->getCollection("orderBy[]=gameTitle-asc{$query}&page=1&limit=".$maxResultCount);
    }

    protected function hydrateObject(RawSingleResourceApiResponseDto $dto): GameVersionDto
    {
        return new GameVersionDto(
            (int) $dto->data['id'],
            (int) $dto->data['platformId'],
            (int) $dto->data['gameId'],
            (int) $dto->data['releaseYear'],
            (bool) $dto->data['todoSoloSometimes'],
            (bool) $dto->data['todoMultiplayerSometimes'],
            (bool) $dto->data['singleplayerRecurring'],
            (bool) $dto->data['multiplayerRecurring'],
            (bool) $dto->data['toDo'],
            (bool) $dto->data['toBuy'],
            (bool) $dto->data['toWatchBackground'],
            (bool) $dto->data['toWatchSerious'],
            (bool) $dto->data['toRewatch'],
            (bool) $dto->data['topGame'],
            (bool) $dto->data['hallOfFame'],
            (int) $dto->data['hallOfFameYear'],
            (int) $dto->data['hallOfFamePosition'],
            (bool) $dto->data['playedItOften'],
            (bool) $dto->data['ongoing'],
            (bool) $dto->data['todoWithHelp'],
            (bool) $dto->data['bestGameForever'],
            (int) $dto->data['toWatchPosition'],
            (int) $dto->data['toDoPosition'],
            (bool) $dto->data['finished'],
            (string) $dto->data['platformName'],
            (string) $dto->data['gameTitle'],
            (int) $dto->data['storyCount'],
            (int) $dto->data['copyCount'],
            isset($dto->data['comments']) ? (string) $dto->data['comments'] : null,
        );
    }
}
