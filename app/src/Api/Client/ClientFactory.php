<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Api\Client;

use Symfony\Component\HttpFoundation\RequestStack;

class ClientFactory
{
    private ?AnonymousClient $anonymousClient = null;
    private ?AuthenticatedClient $authenticatedClient = null;

    public function __construct(
        private readonly string $backendUrl,
        private readonly RequestStack $request,
    ) {
    }

    public function getAnonymousClient(): AnonymousClient
    {
        if (false === $this->anonymousClient instanceof AnonymousClient) {
            $this->anonymousClient = new AnonymousClient($this->backendUrl);
        }

        return $this->anonymousClient;
    }

    public function getAuthenticatedClient(): AuthenticatedClient
    {
        if (false === $this->authenticatedClient instanceof AuthenticatedClient) {
            /** @var string $apiToken */
            $apiToken = $this->request->getSession()->get('apiToken') ?? '';
            $this->authenticatedClient = new AuthenticatedClient($this->backendUrl, $apiToken);
        }

        return $this->authenticatedClient;
    }
}
