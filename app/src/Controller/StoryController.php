<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\GenericApiException;
use App\Service\StoryService;
use App\Service\VersionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class StoryController extends AbstractController
{
    public function __construct(
        private readonly StoryService $service,
        private readonly TranslatorInterface $translator,
        private readonly VersionService $versionService,
    ) {
    }

    #[Route('/stories', name: 'story_list', methods: ['GET'])]
    public function getList(): Response
    {
        $data = $this->service->getList();

        return $this->render(
            'story/list.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        'stories_title',
                        ['%count%' => $data['totalResultCount']]
                    ),
                'screenDescription' => $this->translator->trans('stories_description'),
                'stories' => $data['stories'],
            ]
        );
    }

    #[Route('/story/add', name: 'add_story', methods: ['GET', 'POST']), IsGranted('ROLE_USER')]
    public function add(Request $request): Response
    {
        if ('GET' === $request->getMethod()) {
            return $this->render(
                'story/form.html.twig',
                [
                    'screenTitle' => $this->translator->trans('menu.add_story'),
                    'versions' => $this->versionService->getList()['result'],
                    'selectedVersion' => $request->query->get('version', 0),
                ]
            );
        }

        if (false === $this->isCsrfTokenValid('add_story', $request->request->getString('_csrf_token'))) {
            $this->addFlash('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('add_story');
        }

        $payload = $request->request->all();
        unset($payload['_csrf_token']);

        $this->service->add($payload);

        return $this->redirectToRoute('story_list');
    }

    #[Route('/story/edit/{id<\d+>}', name: 'edit_story', methods: ['GET', 'POST']), IsGranted('ROLE_USER')]
    public function edit(Request $request, int $id): Response
    {
        $story = $this->service->getById($id);

        if ('GET' === $request->getMethod()) {
            return $this->render(
                'story/form.html.twig',
                [
                    'screenTitle' => $this->translator->trans('menu.edit_story'),
                    'versions' => $this->versionService->getList()['result'],
                    'selectedVersion' => $story['versionId'],
                    'story' => $story,
                ]
            );
        }

        if (false === $this->isCsrfTokenValid('add_story', $request->request->getString('_csrf_token'))) {
            $this->addFlash('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('edit_story', ['id' => $id]);
        }

        $payload = $request->request->all();
        unset($payload['_csrf_token']);

        $this->service->update($id, $payload);

        return $this->redirectToRoute('story_list');
    }

    #[Route('/story/delete/{id<\d+>}', name: 'delete_story', methods: ['POST']), IsGranted('ROLE_USER')]
    public function delete(Request $request, int $id): Response
    {
        if (false === $this->isCsrfTokenValid('delete_story', $request->request->getString('_csrf_token'))) {
            $this->addFlash('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('story_list');
        }

        try {
            $this->service->delete($id);
            $this->addFlash('alert', 'entry_deleted_with_success');
        } catch (GenericApiException $exception) {
            if (404 === $exception->getCode()) {
                // Ignore, not a problem because someone might have done it
            }
        }

        return $this->redirectToRoute('story_list');
    }
}
