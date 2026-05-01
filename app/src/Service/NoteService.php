<?php

declare(strict_types=1);

namespace App\Service;

class NoteService extends AbstractService
{
    public function getTotalCount(): int
    {
        /** @var array{totalResultCount: int} $data */
        $data = $this->clientFactory
            ->getAnonymousClient()
            ->get('notes?page=1&limit=1');

        return $data['totalResultCount'];
    }

    /** @return array<string, mixed> */
    public function getList(): array
    {
        return $this->clientFactory
            ->getAnonymousClient()
            ->get('notes?orderBy[]=title-asc&limit='.self::MAX_RESULT_COUNT);
    }

    protected function getResourceType(): string
    {
        return 'note';
    }
}
