<?php

declare(strict_types=1);

namespace App\Service;

use App\Client\ClientFactory;

class HomeService extends AbstractService
{
    public function __construct(
        ClientFactory $clientFactory,
        private VersionService $versionService
    ) {
        parent::__construct($clientFactory);
    }

    public function getHomeData(): array
    {
        // @TODO redacto: use other dedicated services with result limit set to 1?
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

        $ownedVersions =  $this->versionService->getFilteredList('originals');
        $ownedVersionsNotOnCompilation =  $this->versionService->getOriginalsWhereCopyIsNotOnCompilation();
        $responses['originalCount'] = $ownedVersions['totalResultCount'];

        // First chart: owned versions
        $responses['versionsData'] = [];
        foreach ($this->orderForChart($ownedVersions) as $entry) {
            $responses['versionsData'][] = $entry;
        }

        // Second chart: owned versions not on compilation
        $responses['copiesDistributionNotOnCompilationStats'] = [];
        foreach ($this->orderForChart($ownedVersionsNotOnCompilation) as $entry) {
            $responses['copiesDistributionNotOnCompilationStats'][] = $entry;
        }

        return $responses;
    }

    protected function orderForChart(array $data): \Generator
    {
        $tmpVersionData = []; // Because the chat library crashes if there is a key
        foreach ($data['result'] as $entry) {
            $platformId = $entry['platformId'];

            if (false === array_key_exists($platformId, $tmpVersionData)) {
                $tmpVersionData[$platformId] = ['label' => $entry['platformName'], 'y' => 0];
            }

            $tmpVersionData[$platformId]['y']++;
        }

        foreach ($tmpVersionData as $entry) {
            yield $entry;
        }
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
