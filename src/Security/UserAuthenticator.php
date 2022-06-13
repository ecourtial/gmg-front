<?php

namespace App\Security;

use App\Exception\GenericApiException;
use App\Service\UserService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class UserAuthenticator extends AbstractAuthenticator
{
    public function __construct(private readonly UserService $userService, private readonly Router $router)
    {
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning `false` will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request): ?bool
    {
        if ($this->getUsernameFromRequest($request) === '' || $this->getRawPasswordFromRequest($request) === '') {
            return false;
        }

        return true;
    }

    public function authenticate(Request $request): Passport
    {
        try {
            $user = $this->userService->getAuthenticatedUser(
                $this->getUsernameFromRequest($request),
                $this->getRawPasswordFromRequest($request)
            );

            $request->getSession()->set('apiToken', $user->getToken());
        } catch (GenericApiException $exception) {
            if ($exception->getCode() === 403) {
                if ($exception->getApiReturnCode() === 1) {
                    throw new UserNotFoundException();
                }
                if ($exception->getApiReturnCode() === 2) {
                    throw new BadCredentialsException();
                }
                if ($exception->getApiReturnCode() === 3) {
                    throw new DisabledException();
                }
            }

            throw $exception;
        }

        return new SelfValidatingPassport(new UserBadge($user->getUsername()));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = 'authentication.unknown_error';

        if ($exception instanceof UserNotFoundException) {
            $message = 'authentication.user_not_found_error';
        }

        if ($exception instanceof BadCredentialsException) {
            $message = 'authentication.bad_credentials_error';
        }

        if ($exception instanceof DisabledException) {
            $message = 'authentication.inactive_account_error';
        }

        $request->getSession()->getFlashBag()->add('alert', $message);

        return new RedirectResponse($this->router->generate('security_login'));
    }

    private function getUsernameFromRequest(Request $request): string
    {
        return $request->get('_username', '');
    }

    private function getRawPasswordFromRequest(Request $request): string
    {
        return $request->get('_password', '');
    }
}
