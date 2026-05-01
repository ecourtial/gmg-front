<?php

declare(strict_types=1);

namespace App\Service;

class GameService extends AbstractService
{
    /** @return array<string, mixed> */
    public function getList(): array
    {
        /** @var array{result: list<array<string, scalar>>, totalResultCount: int} $data */
        $data = $this->clientFactory
            ->getAnonymousClient()
            ->get('games?orderBy[]=title-asc&limit='.self::MAX_RESULT_COUNT);

        $count = 0;
        foreach ($data['result'] as $game) {
            $count += intval(strval($game['versionCount']));
        }

        $data['versionCount'] = $count;

        return $data;
    }

    /** @return array<string, mixed> */
    public function search(string $keywords): array
    {
        /** @var array{result: list<array<string, scalar>>, totalResultCount: int} $data */
        $data = $this->clientFactory
            ->getAnonymousClient()
            ->get("games?title[]={$keywords}&orderBy[]=title-asc&page=1&limit=".self::MAX_RESULT_COUNT);

        $versionCount = 0;
        foreach ($data['result'] as $result) {
            $versionCount += intval($result['versionCount']);
        }

        $data['versionCount'] = $versionCount;

        return $data;
    }

    protected function getResourceType(): string
    {
        return 'game';
    }
}
