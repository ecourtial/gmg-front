<?php

declare(strict_types=1);

namespace App\Service;

class NoteService extends AbstractService
{
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
