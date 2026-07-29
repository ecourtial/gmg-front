<?php

declare(strict_types=1);

namespace App\Entity\Dto\Specific;

use App\Entity\Dto\GameDto;

readonly class GameDetailsPageDto
{
    public function __construct(
        public GameDto $gameDto,
        public VersionsDataDto $versions,
        public array $mentions,
    ) {
    }
}
