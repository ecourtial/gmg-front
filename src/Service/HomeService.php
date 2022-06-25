<?php

declare(strict_types=1);

namespace App\Service;

class HomeService extends AbstractService
{
    public function getHomeData(): array
    {
        $requests = [
            'gameCount' => $this->clientFactory->getAnonymousClient()->get('games?page=1&limit=1'),
            'versionCount' => $this->clientFactory->getAnonymousClient()->get('versions?page=1&limit=1'),
            'versionFinishedCount' => $this->clientFactory->getAnonymousClient()->get('versions?finished[]=1&page=1&limit=1'),
            'ownedGameCount' => $this->clientFactory->getAnonymousClient()->get('versions?copyCount[]=neq-0&limit=1'),
            'platformCount' => $this->clientFactory->getAnonymousClient()->get('platforms?page=1&limit=1'),
            'toDoCount' => $this->clientFactory->getAnonymousClient()->get('versions?toDo[]=1&page=1&limit=1'),
            'toWatchBackgroundCount' => $this->clientFactory->getAnonymousClient()->get('versions?toWatchBackground[]=1&page=1&limit=1'),
            'toWatchSeriousCount' => $this->clientFactory->getAnonymousClient()->get('versions?toWatchSerious[]=1&page=1&limit=1'),
            'hallOfFameGames' => $this
                ->clientFactory
                ->getAnonymousClient()
                ->get(
                    'versions?hallOfFame[]=1&hallOfFameYear[]=neq-0&hallOfFamePosition[]=neq-0'
                        . '&orderBy[]=hallOfFameYear-asc&orderBy[]=hallOfFamePosition-asc&limit=' . self::MAX_RESULT_COUNT
                ),
        ];

        $responses = [];
        foreach ($requests as $requestName => $request) {
            if ($requestName !== 'hallOfFameGames') {
                $responses[$requestName] = $request['totalResultCount'];
            }
        }

        $responses['toDoSoloOrToWatch'] = $responses['toDoCount'] + $responses['toWatchBackgroundCount']+ $responses['toWatchSeriousCount'];
        $responses['hallOfFameGamesCount'] = \count($requests['hallOfFameGames']['result']);
        $responses['hallOfFameGames'] = $this->orderGames($requests['hallOfFameGames']['result']);

        return $responses;
    }

    protected function getResourceType(): string
    {
        return 'home';
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
