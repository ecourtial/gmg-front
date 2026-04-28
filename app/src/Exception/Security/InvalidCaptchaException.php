<?php

declare(strict_types=1);

namespace App\Exception\Security;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class InvalidCaptchaException extends AuthenticationException
{
    public const ERROR_MSG = 'Invalid captcha response!';

    public function getMessageKey(): string
    {
        return self::ERROR_MSG;
    }
}
