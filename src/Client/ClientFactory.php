<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Client;

use Symfony\Component\HttpFoundation\RequestStack;

class ClientFactory
{
    private ?ReadOnlyClient $readOnlyClient = null;
    private ?ReadWriteClient $readWriteClient = null;

    public function __construct(
        private readonly string $backendUrl,
        private readonly string $readOnlyToken,
        private readonly RequestStack $request
    ) {
    }

    public function getReadOnlyClient(): ReadOnlyClient
    {
        if (false === $this->readOnlyClient instanceof ReadOnlyClient) {
            $this->readOnlyClient = new ReadOnlyClient($this->backendUrl, $this->readOnlyToken);
        }

        return $this->readOnlyClient;
    }

    public function getReadWriteClient(): ReadWriteClient
    {
        if (false === $this->readWriteClient instanceof ReadWriteClient) {
            $this->readWriteClient = new ReadWriteClient(
                $this->backendUrl,
                $this->request->getSession()->get('apiToken')
            );
        }

        return $this->readWriteClient;
    }
}
