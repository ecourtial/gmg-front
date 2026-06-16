<?php

declare(strict_types=1);

namespace App\Service;

class NoteService extends AbstractService
{
    /** @return array<string, mixed> */
    public function getList(int $gameVersionId): array
    {
        $gameVersionIdFilter = '&gameVersionId[]=Null';
        if (0 < $gameVersionId) {
            $gameVersionIdFilter='&gameVersionId[]='.$gameVersionId;
        }

        return $this->clientFactory
            ->getAnonymousClient()
            ->get('notes?orderBy[]=title-asc'.$gameVersionIdFilter.'&limit='.self::MAX_RESULT_COUNT);
    }

    protected function getResourceType(): string
    {
        return 'note';
    }
}
