<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\GenericApiException;
use App\Service\GameService;
use App\Service\NoteService;
use App\Service\VersionService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotesController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly NoteService $service,
    ) {
    }

    #[Route('/notes', methods: ['GET'], name: 'notes_list')]
    public function list(?array $data = null): Response
    {
        $data = $data ?? $this->service->getList();

        return $this->render(
            'note/list.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        'menu.notes',
                        ['%count%' => $data['totalResultCount']]
                    ),
                'notes' => $data['result']
            ]
        );
    }

    #[Route('/note/{id<\d+>}', methods: ['GET'], name: 'note_details')]
    public function get(int $id): Response
    {
        $note = $this->service->getById($id);

        return $this->render(
            'note/details.html.twig',
            [
                'screenTitle' => $note['title'],
                'note' => $note
            ]
        );
    }

    #[Route('/note/delete/{id<\d+>}', methods: ['POST'], name: 'delete_note'), IsGranted('ROLE_USER')]
    public function delete(Request $request, int $id): Response
    {
        if (false === $this->isCsrfTokenValid('delete_note', $request->get('_csrf_token'))) {
            $request->getSession()->getFlashBag()->add('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('note_details', ['id' => $id]);
        }

        try {
            $this->service->delete($id);
            $request->getSession()->getFlashBag()->add('alert', 'entry_deleted_with_success');
        } catch (GenericApiException $exception) {
            if ($exception->getCode() === 404) {
                // Ignore, not a problem because someone might have done it
            }
        }

        return $this->redirectToRoute('notes_list');
    }

    #[Route('/note/add', methods: ['GET', 'POST'], name: 'add_note'), IsGranted('ROLE_USER')]
    public function add(Request $request): Response
    {
        if ($request->getMethod() === 'GET') {
            return $this->render(
                'note/form.html.twig',
                ['screenTitle' => $this->translator->trans('menu.add_note')]
            );
        }

        if (false === $this->isCsrfTokenValid('add_note', $request->get('_csrf_token'))) {
            $request->getSession()->getFlashBag()->add('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('add_note');
        }

        $payload = $request->request->all();
        unset($payload['_csrf_token']);
        $id = $this->service->add($payload)['id'];

        return $this->redirectToRoute('note_details', ['id' => $id]);
    }

    #[Route('/note/edit/{id<\d+>}', methods: ['GET', 'POST'], name: 'edit_note'), IsGranted('ROLE_USER')]
    public function edit(Request $request, int $id): Response
    {
        $note = $this->service->getById($id);

        if ($request->getMethod() === 'GET') {
            return $this->render(
                'note/form.html.twig',
                [
                    'screenTitle' => $this->translator->trans('menu.edit_note', ['%title%' => $note['title']]),
                    'note' => $note
                ]
            );
        }

        if (false === $this->isCsrfTokenValid('add_note', $request->get('_csrf_token'))) {
            $request->getSession()->getFlashBag()->add('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('edit_note', ['id' => $id]);
        }

        $payload = $request->request->all();
        unset($payload['_csrf_token']);

        $this->service->update($id, $payload)['id'];

        return $this->redirectToRoute('note_details', ['id' => $id]);
    }
}
