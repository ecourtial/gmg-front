<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Controller;

use App\Api\Enum\ApiResponseCode;
use App\Exception\GenericApiException;
use App\ResourceService\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Logout\LogoutUrlGenerator;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly UserService $userService,
        private readonly LogoutUrlGenerator $logoutUrlGenerator,
        private readonly AuthenticationUtils $authenticationUtils,
    ) {
    }

    #[Route('/login', name: 'security_login')]
    public function login(string $recaptchaPublicKey): Response
    {
        if ($this->getUser() instanceof UserInterface) {
            return $this->redirectToRoute('homepage');
        }

        return $this->render(
            'user/login_form.html.twig',
            [
                'screenTitle' => $this->translator->trans('user.connexion.title'),
                'recaptcha_key' => $recaptchaPublicKey,
                'last_username' => $this->authenticationUtils->getLastUsername(),
                'error' => $this->authenticationUtils->getLastAuthenticationError(),
            ]
        );
    }

    /**
     * This is the route the user can use to log-out.
     *
     * But, this will never be executed. Symfony will intercept this first
     * and handle the logout automatically. See logout in config/packages/security.yaml
     */
    #[Route('/logout', name: 'security_logout')]
    public function logout(): void
    {
        throw new \Exception('This should never be reached (should be caught and managed by Symfony!');
    }

    #[Route('/change-password', name: 'change_password', methods: ['GET', 'POST']), IsGranted('ROLE_USER')]
    public function changePassword(Request $request): Response
    {
        if ('GET' === $request->getMethod()) {
            return $this->render(
                'user/password_form.html.twig',
                ['screenTitle' => $this->translator->trans('menu.change_password')]
            );
        }

        // Form is submitted
        if (false === $this->isCsrfTokenValid('change_password', $request->request->getString('_csrf_token'))) {
            $this->addFlash('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('change_password');
        }

        $newPassword = $request->request->getString('_new_password');
        $confirmedNewPassword = $request->request->getString('_new_password_confirm');

        if ($newPassword !== $confirmedNewPassword) {
            $this->addFlash('alert', 'user.label.form.new_password_no_match');

            return $this->redirectToRoute('change_password');
        }

        $user = $this->getUser();
        assert($user instanceof \App\Security\User);

        try {
            $this->userService->changeUserPassword(
                $user->getId(),
                $user->getUserIdentifier(),
                $request->request->getString('_current_password'),
                $newPassword
            );

            return $this->redirect($this->logoutUrlGenerator->getLogoutUrl());
        } catch (GenericApiException $exception) {
            if (ApiResponseCode::BAD_CREDENTIALS_API_CODE->value === (int) $exception->getApiReturnCode()) {
                $this->addFlash('alert', 'authentication.bad_current_password');

                return $this->redirectToRoute('change_password');
            }
            throw $exception;
        }
    }
}
