<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Google\CaptchaChecker;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

class UserLoginSubscriber implements EventSubscriberInterface
{
    private Request $request;
    private CaptchaChecker $captchaChecker;

    public function __construct(
        RequestStack $requestStack,
        CaptchaChecker $captchaChecker
    ) {
        $this->request = $requestStack->getCurrentRequest() ?? throw new \RuntimeException('Main request cannot be null');
        $this->captchaChecker = $captchaChecker;
    }

    public static function getSubscribedEvents(): array
    {
        return [CheckPassportEvent::class => 'handleCaptcha'];
    }

    public function handleCaptcha(CheckPassportEvent $event): void
    {
        // The Captcha is only applied when you log in to the admin, not the API
        if (str_starts_with($this->request->getRequestUri(), '/login')) {
            $this->captchaChecker->handleCaptcha(
                $this->request->get('g-recaptcha-response', ''),
                $this->request->getClientIp()
            );
        }
    }
}
