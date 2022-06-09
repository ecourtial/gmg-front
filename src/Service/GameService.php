<?php

declare(strict_types=1);

namespace App\Service;

class GameService extends AbstractService
{
    public CONST FILTERS = [
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
    ];

    public function getVersionById(int $gameId): array
    {
        return $this->clientFactory
            ->getReadOnlyClient()
            ->get("version/{$gameId}");
    }

    public function getByPlatform(int $platformId): array
    {
        $games = $this->clientFactory
            ->getReadOnlyClient()
            ->get("versions?platformId[]={$platformId}&orderBy[]=gameTitle-asc&limit=" . self::MAX_RESULT_COUNT);

        $platform = $this->clientFactory->getReadOnlyClient()->get("platform/{$platformId}");

        return [
            'games' => $games,
            'platform' => $platform
        ];
    }

    public function getFilteredList(string $filter): array
    {
        $filter = self::FILTERS[$filter]['attribute'];

        return $this->clientFactory
            ->getReadOnlyClient()
            ->get("versions?{$filter}[]=1&orderBy[]=gameTitle-asc&limit=" . self::MAX_RESULT_COUNT);
    }

    public function getOriginals(): array
    {
        $copies = $this->clientFactory
            ->getReadOnlyClient()
            ->get("copies?original[]=1&limit=" . self::MAX_RESULT_COUNT);

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
            ->getReadOnlyClient()
            ->get("versions?orderBy[]=gameTitle&{$query}");
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
            ->getReadOnlyClient()
            ->get("versions?{$filter}[]=1'&orderBy[]=rand&limit=1");
    }
}
