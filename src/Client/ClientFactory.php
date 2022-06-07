<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Client;

class ClientFactory
{
    private ?Client $readOnlyClient = null;

    public function __construct(private readonly string $backendUrl, private readonly string $readOnlyToken)
    {
    }

    public function getReadOnlyClient(): Client
    {
        if (false === $this->readOnlyClient instanceof Client) {
            $this->readOnlyClient = new Client($this->backendUrl, $this->readOnlyToken);
        }

        return $this->readOnlyClient;
    }
}
