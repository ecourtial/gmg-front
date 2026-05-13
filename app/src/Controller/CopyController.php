<?php

declare(strict_types=1);

namespace App\Controller;

use App\Api\Enum\ApiResponseCode;
use App\Exception\GenericApiException;
use App\Service\CopyService;
use App\Service\VersionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class CopyController extends AbstractController
{
    public function __construct(
        private readonly CopyService $service,
        private readonly VersionService $versionService,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('/copies/version/{versionId<\d+>}', name: 'copies_per_version', methods: ['GET'])]
    public function getByVersion(int $versionId): Response
    {
        $version = $this->versionService->getById($versionId);
        $copies = $this->service->getByVersion($versionId);

        return $this->render(
            'copy/list.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        'copies_for_version_title',
                        [
                            '%title%' => $version['gameTitle'],
                            '%platform%' => $version['platformName'],
                            '%count%' => $copies['totalResultCount'],
                        ]
                    ),
                'copies' => $copies['result'],
                'versionId' => $versionId,
            ]
        );
    }

    #[Route('/copy/add', name: 'add_copy', methods: ['GET', 'POST']), IsGranted('ROLE_USER')]
    public function add(Request $request): Response
    {
        if ('GET' === $request->getMethod()) {
            return $this->render(
                'copy/form.html.twig',
                [
                    'screenTitle' => $this->translator->trans('menu.add_copy'),
                    'versions' => $this->versionService->getList()['result'],
                    'selectedVersion' => $request->query->get('version', 0),
                ]
            );
        }

        if (false === $this->isCsrfTokenValid('add_copy', $request->request->getString('_csrf_token'))) {
            $this->addFlash('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('add_copy');
        }

        $payload = $request->request->all();
        $payload['status'] = 'In';
        unset($payload['_csrf_token']);

        $copy = $this->service->add($payload);

        return $this->redirectToRoute('version_details', ['id' => $copy['versionId']]);
    }

    #[Route('/copy/edit/{id<\d+>}', name: 'edit_copy', methods: ['GET', 'POST']), IsGranted('ROLE_USER')]
    public function edit(Request $request, int $id): Response
    {
        $copy = $this->service->getById($id);

        if ('GET' === $request->getMethod()) {
            return $this->render(
                'copy/form.html.twig',
                [
                    'screenTitle' => $this->translator->trans('menu.edit_copy'),
                    'versions' => $this->versionService->getList()['result'],
                    'selectedVersion' => $copy['versionId'],
                    'copy' => $copy,
                ]
            );
        }

        if (false === $this->isCsrfTokenValid('add_copy', $request->request->getString('_csrf_token'))) {
            $this->addFlash('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('copies_per_version', ['versionId' => $copy['versionId']]);
        }

        $payload = $request->request->all();
        $payload['status'] = 'In';
        unset($payload['_csrf_token']);

        $this->service->update($id, $payload);

        return $this->redirectToRoute('copies_per_version', ['versionId' => $copy['versionId']]);
    }

    #[Route('/copy/delete/{id<\d+>}', name: 'delete_copy', methods: ['POST']), IsGranted('ROLE_USER')]
    public function delete(Request $request, int $id): Response
    {
        $copy = $this->service->getById($id);

        if (false === $this->isCsrfTokenValid('delete_copy', $request->request->getString('_csrf_token'))) {
            $this->addFlash('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('copies_per_version', ['versionId' => $copy['versionId']]);
        }

        try {
            $this->service->delete($id);
            $this->addFlash('alert', 'entry_deleted_with_success');
        } catch (GenericApiException $exception) {
            if (404 === $exception->getCode()) {
                // Ignore, not a problem because someone might have done it
            } elseif (400 === $exception->getCode() && ApiResponseCode::RESOURCE_HAS_LINKED_RESOURCES->value === $exception->getApiReturnCode()) {
                $this->addFlash('alert', 'version_has_children');

                return $this->redirectToRoute('copies_per_version', ['versionId' => $copy['versionId']]);
            }
        }

        return $this->redirectToRoute('copies_per_version', ['versionId' => $copy['versionId']]);
    }
}
