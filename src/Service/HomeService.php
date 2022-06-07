<?php

declare(strict_types=1);

namespace App\Service;

use App\Client\ClientFactory;

class HomeService
{
    public function __construct(private readonly ClientFactory $clientFactory)
    {
    }

    public function getHomeData(): array
    {
        $requests = [
            'gameCount' => $this->clientFactory->getReadOnlyClient()->get('games?page=1&limit=1'),
            'versionCount' => $this->clientFactory->getReadOnlyClient()->get('versions?page=1&limit=1'),
            'versionFinishedCount' => $this->clientFactory->getReadOnlyClient()->get('versions?finished[]=1&page=1&limit=1'),
            'ownedGameCount' => $this->clientFactory->getReadOnlyClient()->get('versions?copyCount[]=neq-0&limit=1'),
            'platformCount' => $this->clientFactory->getReadOnlyClient()->get('platforms?page=1&limit=1'),
            'toDoCount' => $this->clientFactory->getReadOnlyClient()->get('versions?toDo[]=1&page=1&limit=1'),
            'toWatchBackgroundCount' => $this->clientFactory->getReadOnlyClient()->get('versions?toWatchBackground[]=1&page=1&limit=1'),
            'toWatchSeriousCount' => $this->clientFactory->getReadOnlyClient()->get('versions?toWatchSerious[]=1&page=1&limit=1'),
            'hallOfFameGames' => $this->clientFactory->getReadOnlyClient()->get('versions?hallOfFame[]=1&hallOfFameYear[]=neq-0&hallOfFamePosition[]=neq-0&orderBy[]=hallOfFameYear-asc&orderBy[]=hallOfFamePosition-asc&limit=200'),
        ];

        $responses = [];
        foreach ($requests as $requestName => $request) {
            if ($requestName !== 'hallOfFameGames') {
                $responses[$requestName] = $request['totalResultCount'];
            }
        }

        $responses['toDoSoloOrToWatch'] = $responses['toDoCount'] + $responses['toWatchBackgroundCount']+ $responses['toWatchSeriousCount'];
        $responses['hallOfFameGames'] = $this->orderGames($requests['hallOfFameGames']['result']);

        return $responses;
    }

    private function orderGames(array $games): array
    {
        $data = [];

        foreach ($games as $game) {
            if (false === \array_key_exists($game['hallOfFameYear'], $data)) {
                $data[$game['hallOfFameYear']] = [];
            }

            $data[$game['hallOfFameYear']][] = $game;
        }

        return $data;
    }
}
