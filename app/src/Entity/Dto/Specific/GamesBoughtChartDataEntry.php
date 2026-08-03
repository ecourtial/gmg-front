<?php
declare(strict_types=1);

namespace App\Entity\Dto\Specific;

readonly class GamesBoughtChartDataEntry
{
    public function __construct(public int $x, public int $y) {}
}
