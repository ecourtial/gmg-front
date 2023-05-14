<?php

declare(strict_types=1);

namespace App\Service;

class VersionService extends AbstractService
{
    public const FILTERS = [
        'bgf' => [
            'attribute' => 'bestGameForever',
            'title' => 'best_game_forever_title',
            'description' => 'best_game_forever_description'
        ],
        'solo_recurring' => [
            'attribute' => 'singleplayerRecurring',
            'title' => 'single_player_recurring_title',
            'description' => 'single_player_recurring_description'
        ],
        'multi_recurring' => [
            'attribute' => 'multiplayerRecurring',
            'title' => 'multi_player_recurring_title',
            'description' => 'multi_player_recurring_description'
        ],
        'sometimes_solo' => [
            'attribute' => 'todoSoloSometimes',
            'title' => 'single_player_solo_title',
            'description' => 'single_player_sometimes_description'
        ],
        'multi_sometimes' => [
            'attribute' => 'todoMultiplayerSometimes',
            'title' => 'multi_player_sometimes_title',
            'description' => 'multi_player_sometimes_description'
        ],
        'on_going' => [
            'attribute' => 'ongoing',
            'title' => 'on_going_title',
            'description' => 'on_going_description'
        ],
        'to_buy' => [
            'attribute' => 'toBuy',
            'title' => 'to_buy_title',
            'description' => 'to_buy_description'
        ],
        'finished' => [
            'attribute' => 'finished',
            'title' => 'finished_title',
            'description' => 'finished_description'
        ],
        'not_finished' => [
            'attribute' => 'finished',
            'attribute_value' => 0,
            'title' => 'not_finished_title',
            'description' => 'not_finished_description'
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
    ];

    public const FILTERS_WITH_PRIORITY = [
        'to_do' => [
            'attribute1' => 'toDo',
            'attribute2' => 'toDoPosition',
            'title' => 'to_do_title',
            'description' => 'to_do_description'
        ],
        'to_watch_in_background' => [
            'attribute1' => 'toWatchBackground',
            'attribute2' => 'toWatchPosition',
            'title' => 'to_watch_background_title',
            'description' => 'to_watch_background_description'
        ],
        'to_watch_serious' => [
            'attribute1' => 'toWatchSerious',
            'attribute2' => 'toWatchPosition',
            'title' => 'to_watch_serious_title',
            'description' => 'to_watch_serious_description'
        ],
    ];

    public function getList(int $maxResultCount = self::MAX_RESULT_COUNT): array
    {
        $versions = $this->clientFactory
            ->getAnonymousClient()
            ->get("versions?orderBy[]=gameTitle-asc&page=1&limit=" . $maxResultCount);

        $count = 0;
        foreach ($versions['result'] as $version) {
            if ((int)$version['copyCount'] > 0) {
                $count++;
            }
        }

        $versions['ownedCount'] = $count;

        return $versions;
    }

    public function getByPlatform(int $platformId, int $maxResultCount = self::MAX_RESULT_COUNT): array
    {
        $versions = $this->clientFactory
            ->getAnonymousClient()
            ->get("versions?platformId[]={$platformId}&orderBy[]=gameTitle-asc&page=1&limit=" . $maxResultCount);

        $count = 0;
        foreach ($versions['result'] as $version) {
            if ((int)$version['copyCount'] > 0) {
                $count++;
            }
        }

        return [
            'versions' => $versions,
            'ownedCount' => $count
        ];
    }

    public function getByGame(int $gameId, int $maxResultCount = self::MAX_RESULT_COUNT): array
    {
        $versions = $this->clientFactory
            ->getAnonymousClient()
            ->get("versions?gameId[]={$gameId}&orderBy[]=gameTitle-asc&page=1&limit=" . $maxResultCount);

        $count = 0;
        foreach ($versions['result'] as $version) {
            if ((int)$version['copyCount'] > 0) {
                $count++;
            }
        }

        return [
            'versions' => $versions,
            'ownedCount' => $count
        ];
    }

    public function getFilteredList(string $filter, int $maxResultCount = self::MAX_RESULT_COUNT): array
    {
        $filterValue = (string)(self::FILTERS[$filter]['attribute_value'] ?? '1');
        $needCopies = self::FILTERS[$filter]['filter_from_copies'] ?? false;
        $filterAttribute = self::FILTERS[$filter]['attribute'];

        if ($needCopies) {
            $data = $this->getListFromCopies($filterAttribute, $filterValue, $maxResultCount);
        } else {
            $data = $this->getListFromVersions($filterAttribute, $filterValue, $maxResultCount);
        }

        $count = 0;
        foreach ($data['result'] as $game) {
            if ((int)$game['copyCount'] > 0) {
                $count++;
            }
        }
        $data['ownedCount'] = $count;

        return  $data;
    }

    public function getFilteredListWithPrio(string $filter, int $maxResultCount = self::MAX_RESULT_COUNT): array
    {
        $filter1 = self::FILTERS_WITH_PRIORITY[$filter]['attribute1'];
        $filter2 = self::FILTERS_WITH_PRIORITY[$filter]['attribute2'];

        $result = $this->clientFactory
            ->getAnonymousClient()
            ->get(
                "versions?{$filter1}[]=1&orderBy[]={$filter2}-asc&orderBy[]=gameTitle-asc&page=1&limit=" . $maxResultCount
            );

        $orderedResult = [
            'withPriority' => [],
            'withoutPriority' => [],
        ];

        foreach ($result['result'] as $version) {
            if ($version[$filter2] > 0) {
                $orderedResult['withPriority'][] = $version;
            } else {
                $orderedResult['withoutPriority'][] = $version;
            }
        }

        $count = 0;
        foreach ($orderedResult as $subset) {
            foreach ($subset as $game) {
                if ((int)$game['copyCount'] > 0) {
                    $count++;
                }
            }
        }
        $orderedResult['ownedCount'] = $count;
        $orderedResult['totalResultCount'] = $result['totalResultCount'];

        // Now order by range
        $min = -9;
        $max = 0;
        $range = "$min-$max";
        $versions = [];
        foreach ($orderedResult['withPriority'] as $value) {
            if ($value['toDoPosition'] > $max) {
                $min = $max + 1;
                $max = $min + 9;
                $range = "$min-$max";
                $versions[$range] = [];
            }
            $versions[$range][] = $value;
        }
        $orderedResult['withPriority'] = $versions ;
        // End order by range

        return $orderedResult;
    }

    public function getRandom(string $filter): array
    {
        $soloFilters = ['todoSoloSometimes', 'singleplayerRecurring', 'toDo'];
        $multiFilters = ['todoMultiplayerSometimes', 'multiplayerRecurring'];

        if ($filter === 'singleplayer_random') {
            $filter = $soloFilters[array_rand($soloFilters)];
        } elseif ($filter === 'multiplayer_random') {
            $filter = $multiFilters[array_rand($multiFilters)];
        } elseif (false === \in_array($filter, $soloFilters)
            && false === \in_array($filter, $multiFilters)
        ) {
            return ['totalResultCount' => 0];
        }

        return $this->clientFactory
            ->getAnonymousClient()
            ->get("versions?{$filter}[]=1'&orderBy[]=rand&page=1&limit=1");
    }

    public function search(string $keywords, int $maxResultCount = self::MAX_RESULT_COUNT): array
    {
        $data = $this->clientFactory
            ->getAnonymousClient()
            ->get("versions?gameTitle[]={$keywords}&orderBy[]=gameTitle-asc&page=1&limit=" . $maxResultCount);

        $count = 0;
        foreach ($data['result'] as $game) {
            if ((int)$game['copyCount'] > 0) {
                $count++;
            }
        }
        $data['ownedCount'] = $count;

        return  $data;
    }

    public function getOriginalsWhereCopyIsNotOnCompilation(): array
    {
        return $this->getListFromCopies(
            'onCompilation[]=0&original',
            '1'
        );
    }

    protected function getResourceType(): string
    {
        return 'version';
    }

    protected function getListFromVersions(string $filter, string $filterValue, int $maxResultCount = self::MAX_RESULT_COUNT): array
    {
        return $this->clientFactory
            ->getAnonymousClient()
            ->get("versions?{$filter}[]={$filterValue}&orderBy[]=gameTitle-asc&page=1&limit=" . $maxResultCount);
    }

    protected function getListFromCopies(string $filter, string $filterValue, int $maxResultCount = self::MAX_RESULT_COUNT): array
    {
        // There is a limit of the API here... Consider allowing more accurate filtering
        $copies = $this->clientFactory
            ->getAnonymousClient()
            ->get("copies?{$filter}[]={$filterValue}&orderBy[]=gameTitle-asc&limit=" . self::MAX_RESULT_COUNT);

        $versionIds = [];
        foreach ($copies['result'] as $copy) {
            if ((int)$copy['original'] === 1 && false === \in_array($copy['versionId'], $versionIds)) {
                $versionIds[] = $copy['versionId'];
            }
        }

        if ($versionIds === []) {
            return ['result' => [], 'totalResultCount' => 0];
        }

        $query = '';
        foreach ($versionIds as $id) {
            $query .= '&id[]=' . $id;
        }

        return $this->clientFactory
            ->getAnonymousClient()
            ->get("versions?orderBy[]=gameTitle-asc{$query}&page=1&limit=" . $maxResultCount);
    }
}
