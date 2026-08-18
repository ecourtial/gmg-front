<?php

declare(strict_types=1);

namespace App\Controller;

use App\Api\Enum\ApiResponseCode;
use App\Api\ResourceCollectionResponseDto;
use App\Entity\Dto\Specific\GamesDataDto;
use App\Exception\GenericApiException;
use App\PageService\GamePageService;
use App\ResourceService\GameService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class GameController extends AbstractController
{
    public function __construct(
        private readonly GameService $service,
        private readonly GamePageService $gamePageService,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('/games', name: 'games_list', methods: ['GET'])]
    public function list(): Response
    {
        $data = $this->service->getList();

        return $this->render(
            'game/list.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        'games_list_title',
                        ['%count%' => $data->games->totalResultCount]
                    ),
                'screenSubTitle' => $this->translator
                    ->trans('games_list_subtitle', ['%count%' => $data->ownedCount]),
                'games' => $data->games->result,
            ]
        );
    }

    #[Route('/game/{id<\d+>}', name: 'game_details', methods: ['GET'])]
    public function get(int $id): Response
    {
        $gameDetails = $this->gamePageService->getForDetailsPage($id);

        return $this->render(
            'game/details.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        'game_versions_list',
                        ['%title%' => $gameDetails->gameDto->title],
                    ),
                'screenSubTitle' => $this->translator
                    ->trans(
                        'games_versions_subtitle',
                        [
                            '%resultCount%' => $gameDetails->versions->versions->totalResultCount,
                            '%copyCount%' => $gameDetails->versions->ownedCount,
                        ]
                    ),
                'versions' => $gameDetails->versions->versions->result,
                'game' => $gameDetails->gameDto,
                'mentions' => $gameDetails->mentions,
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
            if (Response::HTTP_NOT_FOUND === $exception->getCode()) {
                // Ignore, not a problem because someone might have done it
            } elseif (Response::HTTP_BAD_REQUEST === $exception->getCode() && ApiResponseCode::RESOURCE_HAS_LINKED_RESOURCES->value === $exception->getApiReturnCode()) {
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
        $id = $this->service->add($payload)->id;

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
                    'screenTitle' => $this->translator->trans('menu.edit_game', ['%title%' => $game->title]),
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
        $data = '' !== $query ? $this->service->search($query) : new GamesDataDto(new ResourceCollectionResponseDto(), 0);

        return $this->render(
            'game/list.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        'search_results',
                        ['%count%' => $data->games->totalResultCount]
                    ),
                'screenSubTitle' => $this->translator->trans(
                    'search_results_subtitle',
                    ['%query%' => $query]
                ),
                'games' => $data->games->result,
            ]
        );
    }

    #[Route('/games/filtered/{filter<\w+>}', name: 'games_filtered_list', methods: ['GET'])]
    public function filteredList(string $filter): Response
    {
        if (false === \array_key_exists($filter, GameService::FILTERS)) {
            throw new NotFoundHttpException();
        }

        $data = $this->gamePageService->getFilteredData($filter);

        return $this->render(
            'game/list.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        'games_with_comments_subtitle',
                        ['%count%' => $data->games->totalResultCount]
                    ),
                'screenSubTitle' => $this->translator
                    ->trans('games_list_subtitle', ['%count%' => $data->ownedCount]),
                'games' => $data->games->result,
            ]
        );
    }
}
