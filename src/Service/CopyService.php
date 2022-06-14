<?php

declare(strict_types=1);

namespace App\Service;

class CopyService extends AbstractService
{
    public const BOX_TYPES = [
        'Big box',
        'DVD',
        'CD',
        'None',
    ];

    public const CASING_TYPES = [
        'DVD',
        'CD',
        'Cardboard sleeve',
        'Paper Sleeve',
        'Plastic Sleeve',
        'None',
    ];

    public const TYPES = [
        'Physical',
        'Virtual',
    ];

    public function getByVersion(int $versionId): array
    {
        return $this->clientFactory
            ->getReadOnlyClient()
            ->get("copies?versionId[]={$versionId}&limit=" . self::MAX_RESULT_COUNT);
    }

    public function add(array $data): array
    {
        return $this->clientFactory->getReadWriteClient()->post(
            'copy',
            [],
            $data
        );
    }

    public function update(int $id, array $data): array
    {
        return $this->clientFactory->getReadWriteClient()->patch(
            'copy/' . $id,
            [],
            $data
        );
    }

    public function delete(int $versionId): void
    {
        parent::removeEntry('copy', $versionId);
    }
}
