<?php

declare(strict_types=1);

namespace App\Entity\Dto\Specific;

use App\Api\ResourceCollectionResponseDto;
use App\Entity\Dto\GameVersionCategoryAssociation;
use App\Entity\Dto\GameVersionDto;
use App\Entity\Dto\NoteDto;

readonly class GameVersionPageDto
{
    /**
     * @param ResourceCollectionResponseDto<NoteDto>                        $notes
     * @param ResourceCollectionResponseDto<GameVersionCategoryAssociation> $categories
     */
    public function __construct(
        public GameVersionDto $gameVersion,
        public TransactionDataDto $transactionData,
        public ResourceCollectionResponseDto $notes,
        public ResourceCollectionResponseDto $categories,
        public GameVersionMentionListDto $mentions,
    ) {
    }
}
