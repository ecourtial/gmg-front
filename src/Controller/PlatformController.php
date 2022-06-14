<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\GenericApiException;
use App\Service\VersionService;
use App\Service\PlatformService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class PlatformController extends AbstractController
{
    public function __construct(
        private readonly PlatformService $service,
        private readonly VersionService $versionService,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route('/platforms', methods: ['GET'], name: 'platforms_list')]
    public function list(): Response
    {
        $data = $this->service->getList();

        return $this->render(
            'platform/list.html.twig',
            [
                'screenTitle' => $this->translator->trans('platforms.title', ['%count%' => $data['totalResultCount']]),
                'data' => $data
            ]
        );
    }

    #[Route('/platform/{id<\d+>}', methods: ['GET'], name: 'platform_details')]
    public function getPlatform(int $id): Response
    {
        $data = $this->versionService->getByPlatform($id);
        $versions = $data['versions'];
        $platform = $this->service->get($id);

        return $this->render(
            'platform/details.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        'games_for_platform_title',
                        [
                            '%name%' => $platform['name'],
                            '%count%' => $versions['totalResultCount']
                        ]
                    ),
                'screenSubTitle' => $this->translator
                    ->trans('have_copy_for_x_of_them', ['%count%' => $data['ownedCount']]),
                'versions' => $versions['result'],
                'platform' => $platform,
            ]
        );
    }

    #[Route('/platform/delete/{id<\d+>}', methods: ['POST'], name: 'delete_platform'), IsGranted('ROLE_USER')]
    public function delete(Request $request, int $id): Response
    {
        if (false === $this->isCsrfTokenValid('delete_platform', $request->get('_csrf_token'))) {
            $request->getSession()->getFlashBag()->add('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('delete_platform', ['id' => $id]);
        }

        try {
            $this->service->delete($id);
            $request->getSession()->getFlashBag()->add('alert', 'entry_deleted_with_success');
        } catch (GenericApiException $exception) {
            if ($exception->getCode() === 404) {
                // Ignore, not a problem because someone might have done it
            } elseif ($exception->getCode() === 400 && $exception->getApiReturnCode() === 9) {
                $request->getSession()->getFlashBag()->add('alert', 'platform_has_versions');

                return $this->redirectToRoute('platform_details', ['id' => $id]);
            }
        }

        return $this->redirectToRoute('platforms_list');
    }

    #[Route('/platform/add', methods: ['GET', 'POST'], name: 'add_platform'), IsGranted('ROLE_USER')]
    public function add(Request $request): Response
    {
        if ($request->getMethod() === 'GET') {
            return $this->render(
                'platform/form.html.twig',
                ['screenTitle' => $this->translator->trans('menu.add_platform')]
            );
        }

        if (false === $this->isCsrfTokenValid('add_platform', $request->get('_csrf_token'))) {
            $request->getSession()->getFlashBag()->add('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('add_platform');
        }

        $id = $this->service->add($request->get('_name'))['id'];

        return $this->redirectToRoute('platform_details', ['id' => $id]);
    }

    #[Route('/platform/edit/{id<\d+>}', methods: ['GET', 'POST'], name: 'edit_platform'), IsGranted('ROLE_USER')]
    public function edit(Request $request, int $id): Response
    {
        $platform = $this->service->get($id);

        if ($request->getMethod() === 'GET') {
            return $this->render(
                'platform/form.html.twig',
                [
                    'screenTitle' => $this->translator->trans('menu.edit_platform', ['%name%' => $platform['name']]),
                    'platform' => $platform
                ]
            );
        }

        if (false === $this->isCsrfTokenValid('add_platform', $request->get('_csrf_token'))) {
            $request->getSession()->getFlashBag()->add('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('edit_platform', ['id' => $id]);
        }

        $id = $this->service->update($id, $request->get('_name'))['id'];

        return $this->redirectToRoute('platform_details', ['id' => $id]);
    }
}
