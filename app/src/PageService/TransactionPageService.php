<?php

declare(strict_types=1);

namespace App\PageService;

use App\Entity\Dto\Specific\CopiesDistributionAmongPlatformsStatsEntry;
use App\Entity\Dto\Specific\GamesBoughtChartDataEntry;
use App\Entity\Dto\Specific\TransactionDataDto;
use App\ResourceService\TransactionService;

readonly class TransactionPageService
{
    public function __construct(private TransactionService $transactionService)
    {
    }

    public function getTransactionsData(int $versionId = 0): TransactionDataDto
    {
        $data = $this->transactionService->getTransactionsData($versionId);

        $result = [
            'totalResultCount' => $data->totalResultCount,
            'transactions' => [],
            'gamesBoughtChartData' => [],
            'copiesDistributionAmongPlatformsStats' => [],
        ];

        // Prepare the list
        foreach ($data->result as $entry) {
            $year = strval($entry->year);
            $month = strval($entry->month);

            if (false === \array_key_exists($year, $result['transactions'])) {
                $result['transactions'][$year] = [];
            }

            if (false === \array_key_exists($month, $result['transactions'][$year])) {
                $result['transactions'][$year][$month] = [];
            }

            $result['transactions'][$year][$month][] = $entry;
        }

        // Prepare the by year repartition chart
        $currentYear = '';
        $currentYearCount = 0;
        foreach ($data->result as $entry) {
            $entryYear = strval($entry->year);
            if ($currentYear !== $entryYear) {
                $currentYear = $entryYear;
                $currentYearCount = 0;
            }
            ++$currentYearCount;

            $date = (string) (new \DateTimeImmutable($currentYear.'-01'))->getTimestamp();
            $date = str_pad($date, 13, '0');
            $result['gamesBoughtChartData'][] = new GamesBoughtChartDataEntry((int) $date, $currentYearCount);
        }

        // Prepare the chart to show purchases distribution among platforms
        $tmpVersionData = [];
        foreach ($data->result as $entry) {
            $platformName = $entry->platformName;

            if (false === array_key_exists($platformName, $tmpVersionData)) {
                $tmpVersionData[$platformName] = ['label' => $platformName, 'y' => 0];
            }

            ++$tmpVersionData[$platformName]['y'];
        }

        foreach ($tmpVersionData as $entry) {
            $result['copiesDistributionAmongPlatformsStats'][] = new CopiesDistributionAmongPlatformsStatsEntry($entry['label'], $entry['y']);
        }

        return new TransactionDataDto(
            $data->totalResultCount,
            $result['transactions'],
            $result['gamesBoughtChartData'],
            $result['copiesDistributionAmongPlatformsStats']
        );
    }
}
