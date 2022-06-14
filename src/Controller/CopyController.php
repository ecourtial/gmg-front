<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\CopyService;
use App\Service\VersionService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class CopyController extends AbstractController
{
    public function __construct(
        private readonly CopyService $service,
        private readonly VersionService $versionService,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route('/copies/version/{versionId<\d+>}', methods: ['GET'], name: 'copies_per_version')]
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
                            '%count%' => $copies['totalResultCount']
                        ]
                    ),
                'copies' => $copies['result']
            ]
        );
    }

    #[Route('/copy/add', methods: ['GET', 'POST'], name: 'add_copy'), IsGranted('ROLE_USER')]
    public function add(Request $request): Response
    {
        if ($request->getMethod() === 'GET') {
            return $this->render(
                'copy/form.html.twig',
                [
                    'screenTitle' => $this->translator->trans('menu.add_copy'),
                    'versions' => $this->versionService->getList()['result'],
                    'selectedVersion' => $request->get('version', 0),
                ]
            );
        }

        if (false === $this->isCsrfTokenValid('add_copy', $request->get('_csrf_token'))) {
            $request->getSession()->getFlashBag()->add('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('add_copy');
        }

        $payload = $request->request->all();
        $payload['status'] = 'In';
        unset($payload['_csrf_token']);

        $copy = $this->service->add($payload);

        return $this->redirectToRoute('version_details', ['id' => $copy['versionId']]);
    }

    #[Route('/copy/edit/{id<\d+>}', methods: ['GET', 'POST'], name: 'edit_copy'), IsGranted('ROLE_USER')]
    public function edit(Request $request, int $id): Response
    {
        $copy = $this->service->getById($id);

        if ($request->getMethod() === 'GET') {
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

        if (false === $this->isCsrfTokenValid('add_copy', $request->get('_csrf_token'))) {
            $request->getSession()->getFlashBag()->add('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('copies_per_version', ['versionId' => $copy['versionId']]);
        }

        $payload = $request->request->all();
        $payload['status'] = 'In';
        unset($payload['_csrf_token']);

        $this->service->update($id, $payload)['id'];

        return $this->redirectToRoute('copies_per_version', ['versionId' => $copy['versionId']]);
    }

}

