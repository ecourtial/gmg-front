<?php

namespace App\Security;

use App\Exception\GenericApiException;
use App\Service\UserService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    public function __construct(private readonly UserService $userService)
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
            $user = $this->userService->getAuthenticatedUser($this->getUsernameFromRequest($request), $this->getRawPasswordFromRequest($request));
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
        $data = [
            // you may want to customize or obfuscate the message first
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())

            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
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
