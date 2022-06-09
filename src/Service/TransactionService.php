<?php

declare(strict_types=1);

namespace App\Service;

class TransactionService extends AbstractService
{
    public function getList(): array
    {
        $data = $this->clientFactory
            ->getReadOnlyClient()
            ->get("transactions?orderBy[]=year-asc&orderBy[]=month-asc&orderBy[]=day-asc&limit=" . self::MAX_RESULT_COUNT);

        $result = [
            'totalResultCount' => $data['totalResultCount'],
            'transactions' => []
        ];

        foreach ($data['result'] as $entry) {
            if (false === \array_key_exists($entry['year'], $result['transactions'])) {
                $result['transactions'][$entry['year']] = [];
            }

            if (false === \array_key_exists($entry['month'], $result['transactions'][$entry['year']])) {
                $result['transactions'][$entry['year']][$entry['month']] = [];
            }

            $result['transactions'][$entry['year']][$entry['month']][] = $entry;
        }

        return $result;
    }
}