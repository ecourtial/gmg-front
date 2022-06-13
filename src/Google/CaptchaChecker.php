<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Google;

use App\Exception\Security\InvalidCaptchaException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CaptchaChecker
{
    private HttpClientInterface $httpClient;
    private string $recaptchaPrivateKey;

    public function __construct(
        HttpClientInterface $httpClient,
        string $recaptchaPrivateKey,
    ) {
        $this->httpClient = $httpClient;
        $this->recaptchaPrivateKey = $recaptchaPrivateKey;
    }

    public function handleCaptcha(string $captchaUserResponse, ?string $userIp): void
    {
        $requestBody = [
            'secret' => $this->recaptchaPrivateKey,
            'response' => $captchaUserResponse,
        ];

        if (is_string($userIp)) {
            $requestBody['remoteip'] = $userIp;
        }

        $googleResponse = $this->httpClient->request(
            'POST',
            'https://www.google.com/recaptcha/api/siteverify',
            ['body' => $requestBody],
        );

        $statusCode = $googleResponse->getStatusCode();

        if ($statusCode !== 200) {
            throw new \RuntimeException(
                'Error when contacting Google for Captcha validation. HTTP Status code was: ' . $statusCode . '.'
            );
        }

        $response = \json_decode($googleResponse->getContent(), true);

        if (false === array_key_exists('success', $response)) {
            throw new \LogicException("Invalid Google response. The 'success' key is missing!");
        }

        /** @var array{'success': bool} $response */
        if ($response['success'] === false) {
            throw new InvalidCaptchaException(InvalidCaptchaException::ERROR_MSG);
        }
    }
}
