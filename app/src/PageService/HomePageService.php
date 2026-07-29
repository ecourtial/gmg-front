<?php

declare(strict_types=1);

namespace App\PageService;

use App\Api\ResourceCollectionResponseDto;
use App\Entity\Dto\GameVersionDto;
use App\ResourceService\CopyService;
use App\ResourceService\GameService;
use App\ResourceService\PlatformService;
use App\ResourceService\VersionService;

readonly class HomePageService
{
    public function __construct(
        private VersionService $versionService,
        private CopyService $copyService,
        private GameService $gameService,
        private PlatformService $platformService,
    ) {
    }

    /** @return array<string, mixed> */
    public function getHomeData(): array
    {
        $requests = [
            'gameCount' => $this->gameService->getFirst(),
            'versionCount' => $this->versionService->getFirst(),
            'versionFinishedCount' => $this->versionService->getFinishedVersionsFirst(),
            'ownedGameCount' => $this->versionService->getOwnedGameFirst(),
            'platformCount' => $this->platformService->getFirst(),
            'toDoCount' => $this->versionService->getTodoFirst(),
            'toWatchBackgroundCount' => $this->versionService->getToWatchInBackgroundFirst(),
            'toWatchSeriousCount' => $this->versionService->getToWatchSeriousFirst(),
            'hallOfFameGames' => $this->versionService->getHallOfFame(),
        ];

        $responses = [];
        foreach ($requests as $requestName => $request) {
            if ('hallOfFameGames' !== $requestName) {
                $responses[$requestName] = $request->totalResultCount;
            }
        }

        $responses['toDoSoloOrToWatch'] = intval($responses['toDoCount']) + intval($responses['toWatchBackgroundCount']) + intval($responses['toWatchSeriousCount']);
        $hallOfFameData = $requests['hallOfFameGames'];
        $responses['hallOfFameGamesCount'] = \count($hallOfFameData->result);
        $responses['hallOfFameGames'] = $this->orderGames($hallOfFameData->result);

        $ownedVersionsNotOnCompilation = $this->versionService->getOriginalsWhereCopyIsNotOnCompilation(
            $this->copyService->getOriginalsWhereCopyIsNotOnCompilation(),
        );

        $originalCopies = $this->copyService->getOriginals();
        $ownedVersions = $this->versionService->getOriginals($originalCopies);
        $responses['originalCount'] = $ownedVersions->totalResultCount;

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

    protected function orderForChart(ResourceCollectionResponseDto $data): \Generator
    {
        $tmpVersionData = []; // Because the chat library crashes if there is a key
        foreach ($data->result as $entry) {
            $platformId = strval($entry->platformId);

            if (false === array_key_exists($platformId, $tmpVersionData)) {
                $tmpVersionData[$platformId] = ['label' => strval($entry->platformName), 'y' => 0];
            }

            ++$tmpVersionData[$platformId]['y'];
        }

        foreach ($tmpVersionData as $entry) {
            yield $entry;
        }
    }

    /**
     * @param GameVersionDto[] $games
     *
     * @return array<int, GameVersionDto>
     */
    private function orderGames(array $games): array
    {
        $data = [];

        foreach ($games as $game) {
            $year = strval($game->hallOfFameYear);
            if (false === \array_key_exists($year, $data)) {
                $data[$year] = [];
            }

            $data[$year][] = $game;
        }

        return $data;
    }
}
