<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\GenericApiException;
use App\Service\GameService;
use App\Service\VersionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class GameController extends AbstractController
{
    public function __construct(
        private readonly GameService $service,
        private readonly VersionService $versionService,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /** @param array<string, mixed>|null $data */
    #[Route('/games', name: 'games_list', methods: ['GET'])]
    public function list(?array $data = null): Response
    {
        $data = $data ?? $this->service->getList();

        return $this->render(
            'game/list.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        'games_list_title',
                        ['%count%' => $data['totalResultCount']]
                    ),
                'screenSubTitle' => $this->translator
                    ->trans('games_list_subtitle', ['%count%' => $data['versionCount']]),
                'games' => $data['result'],
            ]
        );
    }

    #[Route('/game/{id<\d+>}', name: 'game_details', methods: ['GET'])]
    public function get(int $id): Response
    {
        $data = $this->versionService->getByGame($id);
        /** @var array{result: mixed, totalResultCount: mixed, ownedCount: mixed} $versions */
        $versions = $data['versions'];
        $game = $this->service->getById($id);

        return $this->render(
            'game/details.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        'game_versions_list',
                        ['%title%' => $game['title']]
                    ),
                'screenSubTitle' => $this->translator
                    ->trans(
                        'games_versions_subtitle',
                        [
                            '%resultCount%' => $versions['totalResultCount'],
                            '%copyCount%' => $data['ownedCount'],
                        ]
                    ),
                'versions' => $versions['result'],
                'game' => $game,
            ]
        );
    }

    #[Route('/game/delete/{id<\d+>}', name: 'delete_game', methods: ['POST']), IsGranted('ROLE_USER')]
    public function delete(Request $request, int $id): Response
    {
        if (false === $this->isCsrfTokenValid('delete_game', $request->request->getString('_csrf_token'))) {
            $this->addFlash('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('game_details', ['id' => $id]);
        }

        try {
            $this->service->delete($id);
            $this->addFlash('alert', 'entry_deleted_with_success');
        } catch (GenericApiException $exception) {
            if (404 === $exception->getCode()) {
                // Ignore, not a problem because someone might have done it
            } elseif (400 === $exception->getCode() && 9 === $exception->getApiReturnCode()) {
                $this->addFlash('alert', 'games_has_versions');

                return $this->redirectToRoute('game_details', ['id' => $id]);
            }
        }

        return $this->redirectToRoute('games_list');
    }

    #[Route('/game/add', name: 'add_game', methods: ['GET', 'POST']), IsGranted('ROLE_USER')]
    public function add(Request $request): Response
    {
        if ('GET' === $request->getMethod()) {
            return $this->render(
                'game/form.html.twig',
                ['screenTitle' => $this->translator->trans('menu.add_game')]
            );
        }

        if (false === $this->isCsrfTokenValid('add_game', $request->request->getString('_csrf_token'))) {
            $this->addFlash('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('add_game');
        }

        $payload = $request->request->all();
        unset($payload['_csrf_token']);
        $id = $this->service->add($payload)['id'];

        return $this->redirectToRoute('game_details', ['id' => $id]);
    }

    #[Route('/game/edit/{id<\d+>}', name: 'edit_game', methods: ['GET', 'POST']), IsGranted('ROLE_USER')]
    public function edit(Request $request, int $id): Response
    {
        $game = $this->service->getById($id);

        if ('GET' === $request->getMethod()) {
            return $this->render(
                'game/form.html.twig',
                [
                    'screenTitle' => $this->translator->trans('menu.edit_game', ['%title%' => $game['title']]),
                    'game' => $game,
                ]
            );
        }

        if (false === $this->isCsrfTokenValid('add_game', $request->request->getString('_csrf_token'))) {
            $this->addFlash('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('edit_game', ['id' => $id]);
        }

        $payload = $request->request->all();
        unset($payload['_csrf_token']);

        $this->service->update($id, $payload);

        return $this->redirectToRoute('game_details', ['id' => $id]);
    }

    #[Route('/games/search', name: 'game_search', methods: ['POST'])]
    public function search(Request $request): Response
    {
        $query = \trim($request->request->getString('query'));
        $data = '' !== $query ? $this->service->search($query) : ['result' => [], 'totalResultCount' => 0, 'versionCount' => 0];

        return $this->list($data);
    }
}
