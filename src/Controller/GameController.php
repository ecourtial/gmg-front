<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\GenericApiException;
use App\Service\GameService;
use App\Service\VersionService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class GameController extends AbstractController
{
    public function __construct(
        private readonly GameService $service,
        private readonly VersionService $versionService,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route('/games', methods: ['GET'], name: 'games_list')]
    public function list(): Response
    {
        $data = $this->service->getList();

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
                'games' => $data['result']
            ]
        );
    }

    #[Route('/game/{id<\d+>}', methods: ['GET'], name: 'game_details')]
    public function get(int $id): Response
    {
        $data = $this->versionService->getByGame($id);
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
                'game' => $game
            ]
        );
    }

    #[Route('/game/delete/{id<\d+>}', methods: ['POST'], name: 'delete_game'), IsGranted('ROLE_USER')]
    public function delete(Request $request, int $id): Response
    {
        if (false === $this->isCsrfTokenValid('delete_game', $request->get('_csrf_token'))) {
            $request->getSession()->getFlashBag()->add('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('game_details', ['id' => $id]);
        }

        try {
            $this->service->delete($id);
            $request->getSession()->getFlashBag()->add('alert', 'entry_deleted_with_success');
        } catch (GenericApiException $exception) {
            if ($exception->getCode() === 404) {
                // Ignore, not a problem because someone might have done it
            } elseif ($exception->getCode() === 400 && $exception->getApiReturnCode() === 9) {
                $request->getSession()->getFlashBag()->add('alert', 'games_has_versions');

                return $this->redirectToRoute('game_details', ['id' => $id]);
            }
        }

        return $this->redirectToRoute('games_list');
    }

    #[Route('/game/add', methods: ['GET', 'POST'], name: 'add_game'), IsGranted('ROLE_USER')]
    public function add(Request $request): Response
    {
        if ($request->getMethod() === 'GET') {
            return $this->render(
                'game/form.html.twig',
                ['screenTitle' => $this->translator->trans('menu.add_game')]
            );
        }

        if (false === $this->isCsrfTokenValid('add_game', $request->get('_csrf_token'))) {
            $request->getSession()->getFlashBag()->add('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('add_game');
        }

        $payload = $request->request->all();
        unset($payload['_csrf_token']);
        $id = $this->service->add($payload)['id'];

        return $this->redirectToRoute('game_details', ['id' => $id]);
    }

    #[Route('/game/edit/{id<\d+>}', methods: ['GET', 'POST'], name: 'edit_game'), IsGranted('ROLE_USER')]
    public function edit(Request $request, int $id): Response
    {
        $game = $this->service->getById($id);

        if ($request->getMethod() === 'GET') {
            return $this->render(
                'game/form.html.twig',
                [
                    'screenTitle' => $this->translator->trans('menu.edit_game', ['%title%' => $game['title']]),
                    'game' => $game
                ]
            );
        }

        if (false === $this->isCsrfTokenValid('add_game', $request->get('_csrf_token'))) {
            $request->getSession()->getFlashBag()->add('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('edit_game', ['id' => $id]);
        }

        $payload = $request->request->all();
        unset($payload['_csrf_token']);

        $this->service->update($id, $payload)['id'];

        return $this->redirectToRoute('game_details', ['id' => $id]);
    }
}
