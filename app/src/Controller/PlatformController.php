<?php

declare(strict_types=1);

namespace App\Controller;

use App\Api\Enum\ApiResponseCode;
use App\Exception\GenericApiException;
use App\PageService\PlatformPageService;
use App\ResourceService\PlatformService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class PlatformController extends AbstractController
{
    public function __construct(
        private readonly PlatformService $service,
        private readonly PlatformPageService $pageService,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('/platforms', name: 'platforms_list', methods: ['GET'])]
    public function list(): Response
    {
        $data = $this->service->getList();

        return $this->render(
            'platform/list.html.twig',
            [
                'screenTitle' => $this->translator->trans('platforms.title', ['%count%' => $data->totalResultCount]),
                'data' => $data,
            ]
        );
    }

    #[Route('/platform/{id<\d+>}', name: 'platform_details', methods: ['GET'])]
    public function getPlatform(int $id): Response
    {
        $pageDetails = $this->pageService->getPlatformPageDetails($id);

        return $this->render(
            'platform/details.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        'games_for_platform_title',
                        [
                            '%name%' => $pageDetails->platform->name,
                            '%count%' => $pageDetails->versionsData->versions->totalResultCount,
                        ]
                    ),
                'screenSubTitle' => $this->translator
                    ->trans('have_copy_for_x_of_them', ['%count%' => $pageDetails->versionsData->ownedCount]),
                'versions' => $pageDetails->versionsData->versions->result,
                'platform' => $pageDetails->platform,
            ]
        );
    }

    #[Route('/platform/delete/{id<\d+>}', name: 'delete_platform', methods: ['POST']), IsGranted('ROLE_USER')]
    public function delete(Request $request, int $id): Response
    {
        if (false === $this->isCsrfTokenValid('delete_platform', $request->request->getString('_csrf_token'))) {
            $this->addFlash('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('platform_details', ['id' => $id]);
        }

        try {
            $this->service->delete($id);
            $this->addFlash('alert', 'entry_deleted_with_success');
        } catch (GenericApiException $exception) {
            if (Response::HTTP_NOT_FOUND === $exception->getCode()) {
                // Ignore, not a problem because someone might have done it
            } elseif (Response::HTTP_BAD_REQUEST === $exception->getCode() && ApiResponseCode::RESOURCE_HAS_LINKED_RESOURCES->value === $exception->getApiReturnCode()) {
                $this->addFlash('alert', 'platform_has_versions');

                return $this->redirectToRoute('platform_details', ['id' => $id]);
            }
        }

        return $this->redirectToRoute('platforms_list');
    }

    #[Route('/platform/add', name: 'add_platform', methods: ['GET', 'POST']), IsGranted('ROLE_USER')]
    public function add(Request $request): Response
    {
        if ('GET' === $request->getMethod()) {
            return $this->render(
                'platform/form.html.twig',
                ['screenTitle' => $this->translator->trans('menu.add_platform')]
            );
        }

        if (false === $this->isCsrfTokenValid('add_platform', $request->request->getString('_csrf_token'))) {
            $this->addFlash('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('add_platform');
        }

        $payload = $request->request->all();
        unset($payload['_csrf_token']);

        $id = $this->service->add($payload)->id;

        return $this->redirectToRoute('platform_details', ['id' => $id]);
    }

    #[Route('/platform/edit/{id<\d+>}', name: 'edit_platform', methods: ['GET', 'POST']), IsGranted('ROLE_USER')]
    public function edit(Request $request, int $id): Response
    {
        $platform = $this->service->getById($id);

        if ('GET' === $request->getMethod()) {
            return $this->render(
                'platform/form.html.twig',
                [
                    'screenTitle' => $this->translator->trans('menu.edit_platform', ['%name%' => $platform->name]),
                    'platform' => $platform,
                ]
            );
        }

        if (false === $this->isCsrfTokenValid('add_platform', $request->request->getString('_csrf_token'))) {
            $this->addFlash('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('edit_platform', ['id' => $id]);
        }

        $payload = $request->request->all();
        unset($payload['_csrf_token']);

        $this->service->update($id, $payload);

        return $this->redirectToRoute('platform_details', ['id' => $id]);
    }
}
