<?php
declare(strict_types=1);

namespace App\Entity\Dto;

readonly class TransactionDto
{
    public function __construct(
        public int $id,
        public int $versionId,
        public ?int $copyId = null,
        public int $year,
        public int $month,
        public int $day,
        public string $type,
        public ?string $notes = null,
        public string $platformName,
        public string $gameTitle,
    ){}
}
