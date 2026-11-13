<?php

declare(strict_types=1);

namespace App\Entity\Dto\Specific;

use App\Entity\Dto\TransactionDto;

readonly class TransactionDataDto
{
    /**
     * @param array<int, array<int, list<TransactionDto>>> $transactions
     * @param GamesBoughtChartDataEntry[]                  $gamesBoughtChartData
     * @param CopiesDistributionAmongPlatformsStatsEntry[] $copiesDistributionAmongPlatformsStats
     */
    public function __construct(
        public int $totalResultCount,
        public array $transactions,
        public array $gamesBoughtChartData,
        public array $copiesDistributionAmongPlatformsStats,
    ) {
    }
}
