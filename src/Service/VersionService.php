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

    public function getList(): array
    {
        $versions = $this->clientFactory
            ->getAnonymousClient()
            ->get("versions?orderBy[]=gameTitle-asc&limit=" . self::MAX_RESULT_COUNT);

        $count = 0;
        foreach ($versions['result'] as $version) {
            if ((int)$version['copyCount'] > 0) {
                $count++;
            }
        }

        $versions['ownedCount'] = $count;

        return $versions;
    }

    public function getByPlatform(int $platformId): array
    {
        $versions = $this->clientFactory
            ->getAnonymousClient()
            ->get("versions?platformId[]={$platformId}&orderBy[]=gameTitle-asc&limit=" . self::MAX_RESULT_COUNT);

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

    public function getByGame(int $gameId): array
    {
        $versions = $this->clientFactory
            ->getAnonymousClient()
            ->get("versions?gameId[]={$gameId}&orderBy[]=gameTitle-asc&limit=" . self::MAX_RESULT_COUNT);

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

    public function getFilteredList(string $filter): array
    {
        $filter = self::FILTERS[$filter]['attribute'];

        $data = $this->clientFactory
            ->getAnonymousClient()
            ->get("versions?{$filter}[]=1&orderBy[]=gameTitle-asc&limit=" . self::MAX_RESULT_COUNT);

        $count = 0;
        foreach ($data['result'] as $game) {
            if ((int)$game['copyCount'] > 0) {
                $count++;
            }
        }
        $data['ownedCount'] = $count;

        return  $data;
    }

    public function getFilteredListWithPrio(string $filter): array
    {
        $filter1 = self::FILTERS_WITH_PRIORITY[$filter]['attribute1'];
        $filter2 = self::FILTERS_WITH_PRIORITY[$filter]['attribute2'];

        $result = $this->clientFactory
            ->getAnonymousClient()
            ->get(
                "versions?{$filter1}[]=1&orderBy[]={$filter2}-asc&orderBy[]=gameTitle-asc&limit=" . self::MAX_RESULT_COUNT
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

        return $orderedResult;
    }

    public function getOriginals(): array
    {
        $copies = $this->clientFactory
            ->getAnonymousClient()
            ->get("copies?original[]=1&orderBy[]=gameTitle-asc&limit=" . self::MAX_RESULT_COUNT);

        $versionIds = [];
        foreach ($copies['result'] as $copy) {
            if ((int)$copy['original'] === 1 && false === \in_array($copy['versionId'], $versionIds)) {
                $versionIds[] = $copy['versionId'];
            }
        }

        if ($versionIds === []) {
            return ['totalResultCount' => 0];
        }

        $query = '';
        foreach ($versionIds as $id) {
            $query .= '&id[]=' . $id;
        }

        return $this->clientFactory
            ->getAnonymousClient()
            ->get("versions?orderBy[]=gameTitle-asc&{$query}");
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
            ->get("versions?{$filter}[]=1'&orderBy[]=rand&limit=1");
    }

    public function search(string $keywords): array
    {
        $data = $this->clientFactory
            ->getAnonymousClient()
            ->get("versions?gameTitle[]={$keywords}&orderBy[]=gameTitle-asc&limit=" . self::MAX_RESULT_COUNT);

        $count = 0;
        foreach ($data['result'] as $game) {
            if ((int)$game['copyCount'] > 0) {
                $count++;
            }
        }
        $data['ownedCount'] = $count;

        return  $data;
    }

    protected function getResourceType(): string
    {
        return 'version';
    }
}
