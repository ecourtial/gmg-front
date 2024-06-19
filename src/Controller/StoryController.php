<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\GenericApiException;
use App\Service\StoryService;
use App\Service\VersionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class StoryController extends AbstractController
{
    public function __construct(
        private readonly StoryService $service,
        private readonly TranslatorInterface $translator,
        private readonly VersionService $versionService,
    ) {
    }

    #[Route('/stories', methods: ['GET'], name: 'story_list')]
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
                'stories' => $data['stories']
            ]
        );
    }

    #[Route('/story/add', methods: ['GET', 'POST'], name: 'add_story'), IsGranted('ROLE_USER')]
    public function add(Request $request): Response
    {
        if ($request->getMethod() === 'GET') {
            return $this->render(
                'story/form.html.twig',
                [
                    'screenTitle' => $this->translator->trans('menu.add_story'),
                    'versions' => $this->versionService->getList()['result'],
                    'selectedVersion' => $request->get('version', 0),
                ]
            );
        }

        if (false === $this->isCsrfTokenValid('add_story', $request->get('_csrf_token'))) {
            $request->getSession()->getFlashBag()->add('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('add_story');
        }

        $payload = $request->request->all();
        unset($payload['_csrf_token']);

        $this->service->add($payload)['id'];

        return $this->redirectToRoute('story_list');
    }

    #[Route('/story/edit/{id<\d+>}', methods: ['GET', 'POST'], name: 'edit_story'), IsGranted('ROLE_USER')]
    public function edit(Request $request, int $id): Response
    {
        $story = $this->service->getById($id);

        if ($request->getMethod() === 'GET') {
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

        if (false === $this->isCsrfTokenValid('add_story', $request->get('_csrf_token'))) {
            $request->getSession()->getFlashBag()->add('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('edit_story', ['id' => $id]);
        }

        $payload = $request->request->all();
        unset($payload['_csrf_token']);

        $this->service->update($id, $payload)['id'];

        return $this->redirectToRoute('story_list');
    }

    #[Route('/story/delete/{id<\d+>}', methods: ['POST'], name: 'delete_story'), IsGranted('ROLE_USER')]
    public function delete(Request $request, int $id): Response
    {
        if (false === $this->isCsrfTokenValid('delete_story', $request->get('_csrf_token'))) {
            $request->getSession()->getFlashBag()->add('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('story_list');
        }

        try {
            $this->service->delete($id);
            $request->getSession()->getFlashBag()->add('alert', 'entry_deleted_with_success');
        } catch (GenericApiException $exception) {
            if ($exception->getCode() === 404) {
                // Ignore, not a problem because someone might have done it
            }
        }

        return $this->redirectToRoute('story_list');
    }
}
