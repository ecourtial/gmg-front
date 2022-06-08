<?php

declare(strict_types=1);

namespace App\Service;

class GameService extends AbstractService
{
    public CONST FILTERS = [
        'toto' => 'toto.translation.key'
    ];

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
        if (false === \array_key_exists($filter, self::FILTERS)) {
            return [];
        }

        return $this->clientFactory
            ->getReadOnlyClient()
            ->get('platforms?orderBy[]=name-asc&limit=' . self::MAX_RESULT_COUNT);
    }
}
