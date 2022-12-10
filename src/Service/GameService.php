<?php

declare(strict_types=1);

namespace App\Service;

class GameService extends AbstractService
{
    public function getList(): array
    {
        $data = $this->clientFactory
            ->getAnonymousClient()
            ->get("games?orderBy[]=title-asc&limit=" . self::MAX_RESULT_COUNT);

        $count = 0;
        foreach ($data['result'] as $game) {
            $count += $game['versionCount'];
        }

        $data['versionCount'] = $count;

        return $data;
    }

    public function search(string $keywords): array
    {
        $data = $this->clientFactory
            ->getAnonymousClient()
            ->get("games?title[]={$keywords}&orderBy[]=title-asc&page=1&limit=" . self::MAX_RESULT_COUNT);

        $versionCount = 0;
        foreach ($data['result'] as $result) {
            $versionCount += $result['versionCount'];
        }

        $data['versionCount'] = $versionCount;

        return $data;
    }

    protected function getResourceType(): string
    {
        return 'game';
    }
}
