<?php

declare(strict_types=1);

namespace App\ResourceService;

use App\Api\RawSingleResourceApiResponseDto;
use App\Api\ResourceCollectionResponseDto;
use App\Entity\Dto\NoteDto;

/**
 * @extends AbstractService<NoteDto>
 */
class NoteService extends AbstractService
{
    /**
     * @return ResourceCollectionResponseDto<NoteDto>
     */
    public function getList(int $gameVersionId): ResourceCollectionResponseDto
    {
        $gameVersionIdFilter = '&gameVersionId[]=Null';
        if (0 < $gameVersionId) {
            $gameVersionIdFilter = '&gameVersionId[]='.$gameVersionId;
        }

        return $this->getCollection('orderBy[]=title-asc'.$gameVersionIdFilter.'&limit='.self::MAX_RESULT_COUNT);
    }

    protected function getResourceNamePlural(): string
    {
        return 'notes';
    }

    protected function hydrateObject(RawSingleResourceApiResponseDto $dto): NoteDto
    {
        return new NoteDto(
            (int) $dto->data['id'],
            (string) $dto->data['title'],
            (string) $dto->data['content'],
            (int) $dto->data['gameVersionId'],
        );
    }
}
