<?php

declare(strict_types=1);

namespace App\Service;

class PlatformService extends AbstractService
{
    public function get(int $id): array
    {
        return $this->clientFactory->getReadOnlyClient()->get("platform/{$id}");
    }

    public function getList(): array
    {
        return $this->clientFactory
            ->getReadOnlyClient()
            ->get('platforms?orderBy[]=name-asc&limit=' . self::MAX_RESULT_COUNT);
    }

    public function add(string $name): array
    {
        return $this->clientFactory->getReadWriteClient()->post(
            'platform',
            [],
            ['name' => $name]
        );
    }

    public function update(int $id, string $name): array
    {
        return $this->clientFactory->getReadWriteClient()->patch(
            'platform/' . $id,
            [],
            ['name' => $name]
        );
    }

    public function delete(int $platformId): void
    {
        parent::removeEntry('platform', $platformId);
    }
}
