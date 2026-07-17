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

        return $this->hydrateResultCollection(
            $this->clientFactory
                ->getAnonymousClient()
                ->get('notes?orderBy[]=title-asc'.$gameVersionIdFilter.'&limit='.self::MAX_RESULT_COUNT)
        );
    }

    protected function getResourceType(): string
    {
        return 'note';
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
