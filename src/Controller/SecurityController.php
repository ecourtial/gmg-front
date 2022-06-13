<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Controller;

use App\Exception\GenericApiException;
use App\Service\UserService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Logout\LogoutUrlGenerator;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly UserService $userService,
        private readonly LogoutUrlGenerator $logoutUrlGenerator,
    ) {
    }

    #[Route('/login', name: 'security_login')]
    public function login(string $recaptchaPublicKey, AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('homepage');
        }

        return $this->render(
            'user/login_form.html.twig',
            [
                'screenTitle' => $this->translator->trans('user.connexion.title'),
                'recaptcha_key' => $recaptchaPublicKey,
                'last_username' => $authenticationUtils->getLastUsername(),
                'error' => $authenticationUtils->getLastAuthenticationError()
            ]
        );
    }

    /**
     * This is the route the user can use to logout.
     *
     * But, this will never be executed. Symfony will intercept this first
     * and handle the logout automatically. See logout in config/packages/security.yaml
     */
    #[Route('/logout', name: 'security_logout')]
    public function logout(): void
    {
        throw new \Exception('This should never be reached (should be caught and managed by Symfony!');
    }

    #[Route('/change-password', methods: ['GET'], name: 'change_password_form'), IsGranted('ROLE_USER')]
    public function changePasswordForm(Request $request): Response
    {
        return $this->render(
            'user/password_form.html.twig',
            ['screenTitle' => $this->translator->trans('menu.change_password')]
        );
    }

    #[Route('/change-password', methods: ['POST'], name: 'change_password'), IsGranted('ROLE_USER')]
    public function changePassword(Request $request): Response
    {
        if (false === $this->isCsrfTokenValid('change_password', $request->get('_csrf_token'))) {
            $request->getSession()->getFlashBag()->add('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('change_password');
        }

        $newPassword = $request->get('_new_password');
        $confirmedNewPassword = $request->get('_new_password_confirm');

        if ($newPassword !== $confirmedNewPassword) {
            $request->getSession()->getFlashBag()->add('alert', 'user.label.form.new_password_no_match');

            return $this->redirectToRoute('change_password');
        }

        try {
            $this->userService->changePassword(
                $this->getUser()->getId(),
                $this->getUser()->getUserIdentifier(),
                $request->get('_current_password'),
                $newPassword
            );

            return $this->redirect($this->logoutUrlGenerator->getLogoutUrl());
        } catch (GenericApiException $exception) {
            if ($exception->getApiReturnCode() === 2) {
                $request->getSession()->getFlashBag()->add('alert', 'authentication.bad_current_password');

                return $this->redirectToRoute('change_password');
            } else {
                throw $exception;
            }
        }
    }
}
