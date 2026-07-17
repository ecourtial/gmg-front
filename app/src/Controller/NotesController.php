<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\GenericApiException;
use App\ResourceService\NoteService;
use App\ResourceService\VersionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotesController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly NoteService $service,
        private readonly VersionService $versionService,
    ) {
    }

    #[Route('/notes', name: 'notes_list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        $gameVersionId = (int)$request->query->get('gameVersionId', 0);
        $data = $this->service->getList($gameVersionId);

        return $this->render(
            'note/list.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        'menu.global_notes',
                        ['%count%' => $data->totalResultCount]
                    ),
                'notes' => $data->result,
            ]
        );
    }

    #[Route('/note/{id<\d+>}', name: 'note_details', methods: ['GET']), IsGranted('ROLE_USER')]
    public function get(int $id): Response
    {
        $note = $this->service->getById($id);

        return $this->render(
            'note/details.html.twig',
            [
                'screenTitle' => $note->title,
                'note' => $note,
            ]
        );
    }

    #[Route('/note/delete/{id<\d+>}', name: 'delete_note', methods: ['POST']), IsGranted('ROLE_USER')]
    public function delete(Request $request, int $id): Response
    {
        if (false === $this->isCsrfTokenValid('delete_note', $request->request->getString('_csrf_token'))) {
            $this->addFlash('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('note_details', ['id' => $id]);
        }

        try {
            $this->service->delete($id);
            $this->addFlash('alert', 'entry_deleted_with_success');
        } catch (GenericApiException $exception) {
            if (Response::HTTP_NOT_FOUND === $exception->getCode()) {
                // Ignore, not a problem because someone else might have done it in the meantime.
            }
        }

        return $this->redirectToRoute('notes_list');
    }

    #[Route('/note/add', name: 'add_note', methods: ['GET', 'POST']), IsGranted('ROLE_USER')]
    public function add(Request $request): Response
    {
        if ('GET' === $request->getMethod()) {
            $gameVersionId = (int)$request->query->get('gameVersionId', 0);
            $payload = ['screenTitle' => $this->translator->trans('menu.add_note')];

            if (0 < $gameVersionId) {
                $version = $this->versionService->getById($gameVersionId);
                $payload['gameVersionId'] = $gameVersionId;
                $payload['screenTitle'] = $this->translator->trans('add_note_for_game_version');
                $payload['screenSubTitle'] = $version->gameTitle;
            }

            return $this->render(
                'note/form.html.twig',
                $payload
            );
        }

        if (false === $this->isCsrfTokenValid('add_note', $request->request->getString('_csrf_token'))) {
            $this->addFlash('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('add_note');
        }

        $payload = $request->request->all();
        unset($payload['_csrf_token']);
        $id = $this->service->add($payload)->id;

        return $this->redirectToRoute('note_details', ['id' => $id]);
    }

    #[Route('/note/edit/{id<\d+>}', name: 'edit_note', methods: ['GET', 'POST']), IsGranted('ROLE_USER')]
    public function edit(Request $request, int $id): Response
    {
        $note = $this->service->getById($id);

        if ('GET' === $request->getMethod()) {
            return $this->render(
                'note/form.html.twig',
                [
                    'screenTitle' => $this->translator->trans('menu.edit_note', ['%title%' => $note->title]),
                    'note' => $note,
                ]
            );
        }

        if (false === $this->isCsrfTokenValid('add_note', $request->request->getString('_csrf_token'))) {
            $this->addFlash('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('edit_note', ['id' => $id]);
        }

        $payload = $request->request->all();
        unset($payload['_csrf_token']);

        $this->service->update($id, $payload);

        return $this->redirectToRoute('note_details', ['id' => $id]);
    }
}
