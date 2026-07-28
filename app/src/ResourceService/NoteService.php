<?php

declare(strict_types=1);

namespace App\ResourceService;

use App\Api\ResourceCollectionResponseDto;
use App\Entity\Dto\NoteDto;

/**
 * @extends AbstractService<NoteDto>
 */
class NoteService extends AbstractService
{
    public function getList(int $gameVersionId): ResourceCollectionResponseDto
    {
        $gameVersionIdFilter = '&gameVersionId[]=Null';
        if (0 < $gameVersionId) {
            $gameVersionIdFilter='&gameVersionId[]='.$gameVersionId;
        }

        return $this->getCollection('?orderBy[]=title-asc'.$gameVersionIdFilter.'&limit='.self::MAX_RESULT_COUNT);
    }

    protected function getResourceNamePlural(): string
    {
        return 'notes';
    }

    protected function hydrateObject(array $data): NoteDto
    {
        return new NoteDto(
            $data['id'],
            $data['title'],
            $data['content'],
            $data['gameVersionId'],
        );
    }
}
