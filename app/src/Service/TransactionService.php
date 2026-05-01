<?php

declare(strict_types=1);

namespace App\Service;

class TransactionService extends AbstractService
{
    /** @return array<string, mixed> */
    public function getList(): array
    {
        /** @var array{result: list<array<string, scalar>>, totalResultCount: int} $data */
        $data = $this->clientFactory
            ->getAnonymousClient()
            ->get('transactions?orderBy[]=year-asc&orderBy[]=month-asc&orderBy[]=day-asc&limit='.self::MAX_RESULT_COUNT);

        $result = [
            'totalResultCount' => $data['totalResultCount'],
            'transactions' => [],
            'gamesBoughtChartData' => [],
            'copiesDistributionAmongPlatformsStats' => [],
        ];

        // Prepare the list
        foreach ($data['result'] as $entry) {
            $year = strval($entry['year']);
            $month = strval($entry['month']);

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
        foreach ($data['result'] as $entry) {
            $entryYear = strval($entry['year']);
            if ($currentYear !== $entryYear) {
                $currentYear = $entryYear;
                $currentYearCount = 0;
            }
            ++$currentYearCount;

            $date = (string) (new \DateTimeImmutable($currentYear.'-01'))->getTimestamp();
            $date = str_pad($date, 13, '0');
            $result['gamesBoughtChartData'][] = ['x' => (int) $date, 'y' => $currentYearCount];
        }

        // Prepare the chart to show purchases distribution among platforms
        $tmpVersionData = [];
        foreach ($data['result'] as $entry) {
            $platformName = strval($entry['platformName']);

            if (false === array_key_exists($platformName, $tmpVersionData)) {
                $tmpVersionData[$platformName] = ['label' => $platformName, 'y' => 0];
            }

            ++$tmpVersionData[$platformName]['y'];
        }

        foreach ($tmpVersionData as $entry) {
            $result['copiesDistributionAmongPlatformsStats'][] = $entry;
        }

        return $result;
    }

    protected function getResourceType(): string
    {
        return 'transaction';
    }
}
