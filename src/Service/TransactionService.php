<?php

declare(strict_types=1);

namespace App\Service;

class TransactionService extends AbstractService
{
    public function getList(): array
    {
        $data = $this->clientFactory
            ->getAnonymousClient()
            ->get("transactions?orderBy[]=year-asc&orderBy[]=month-asc&orderBy[]=day-asc&limit=" . self::MAX_RESULT_COUNT);

        $result = [
            'totalResultCount' => $data['totalResultCount'],
            'transactions' => [],
            'gamesBoughtChartData' => [],
        ];

        // Prepare the list
        foreach ($data['result'] as $entry) {
            if (false === \array_key_exists($entry['year'], $result['transactions'])) {
                $result['transactions'][$entry['year']] = [];
            }

            if (false === \array_key_exists($entry['month'], $result['transactions'][$entry['year']])) {
                $result['transactions'][$entry['year']][$entry['month']] = [];
            }

            $result['transactions'][$entry['year']][$entry['month']][] = $entry;
        }

        // Prepare the by year repartition chart
        $currentYear = null;
        $currentYearCount = 0;
        foreach ($data['result'] as $entry) {
            if ($currentYear !== $entry['year']) {
                $currentYear = $entry['year'];
                $currentYearCount = 0;
            }
            $currentYearCount++;

            $date = (string)(new \DateTimeImmutable((string)$currentYear . '-01'))->getTimestamp();
            $date = str_pad($date, 13, '0');
            $result['gamesBoughtChartData'][] = ['x' => (int)$date, 'y' => $currentYearCount];
        }

        return $result;
    }

    protected function getResourceType(): string
    {
        return 'transaction';
    }
}
