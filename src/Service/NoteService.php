<?php

declare(strict_types=1);

namespace App\Service;

class NoteService extends AbstractService
{
    public function getTotalCount(): int
    {
        $data = $this->clientFactory
            ->getAnonymousClient()
            ->get("notes?page=1&limit=1");

        return (int)$data['totalResultCount'];
    }

    public function getList(): array
    {
        return $this->clientFactory
            ->getAnonymousClient()
            ->get("notes?orderBy[]=title-asc&limit=" . self::MAX_RESULT_COUNT);
    }

    protected function getResourceType(): string
    {
        return 'note';
    }
}
