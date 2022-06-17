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

    protected function getResourceType(): string
    {
        return 'game';
    }
}
