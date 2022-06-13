<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Client;

class ClientFactory
{
    private ?ReadOnlyClient $readOnlyClient = null;

    public function __construct(private readonly string $backendUrl, private readonly string $readOnlyToken)
    {
    }

    public function getReadOnlyClient(): ReadOnlyClient
    {
        if (false === $this->readOnlyClient instanceof ReadOnlyClient) {
            $this->readOnlyClient = new ReadOnlyClient($this->backendUrl, $this->readOnlyToken);
        }

        return $this->readOnlyClient;
    }
}
