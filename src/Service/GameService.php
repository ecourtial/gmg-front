<?php

declare(strict_types=1);

namespace App\Service;

class GameService extends AbstractService
{
    public function get(int $id): array
    {
        return $this->clientFactory->getReadOnlyClient()->get("game/{$id}");
    }

    public function getList(): array
    {
        $data = $this->clientFactory
            ->getReadOnlyClient()
            ->get("games?orderBy[]=title-asc&limit=" . self::MAX_RESULT_COUNT);

        $count = 0;
        foreach ($data['result'] as $game) {
            $count += $game['versionCount'];
        }

        $data['versionCount'] = $count;

        return $data;
    }

    public function add(string $title, string $notes): array
    {
        return $this->clientFactory->getReadWriteClient()->post(
            'game',
            [],
            ['title' => $title, 'notes' => $notes]
        );
    }

    public function update(int $id, string $title, string $notes): array
    {
        return $this->clientFactory->getReadWriteClient()->patch(
            'game/' . $id,
            [],
            ['title' => $title, 'notes' => $notes]
        );
    }

    public function delete(int $gameId): void
    {
        parent::removeEntry('game', $gameId);
    }
}
