<?php

declare(strict_types=1);

namespace App\Entity\Dto;

readonly class TransactionDto
{
    public function __construct(
        public int $id,
        public int $versionId,
        public int $year,
        public int $month,
        public int $day,
        public string $type,
        public string $platformName,
        public string $gameTitle,
        public ?int $copyId = null,
        public ?string $notes = null,
    ) {
    }
}
