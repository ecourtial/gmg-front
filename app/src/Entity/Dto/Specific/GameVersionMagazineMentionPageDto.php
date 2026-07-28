<?php
declare(strict_types=1);

namespace App\Entity\Dto\Specific;

class GameVersionMagazineMentionPageDto
{
    public function __construct(
        public int $id,
        public int $gameVersionId,
        public string $gameTitle,
        public int $pageNumber,
        public ?string $notes = null,
    ) {}
}
