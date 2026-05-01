<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\GenericApiException;
use App\Service\GameService;
use App\Service\PlatformService;
use App\Service\VersionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class VersionController extends AbstractController
{
    public function __construct(
        private readonly VersionService $service,
        private readonly TranslatorInterface $translator,
        private readonly GameService $gameService,
        private readonly PlatformService $platformService,
    ) {
    }

    #[Route('/version/{id<\d+>}', name: 'version_details', methods: ['GET'])]
    public function versionDetails(int $id): Response
    {
        $version = $this->service->getById($id);

        return $this->render(
            'version/details.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        'game.version_details',
                        [
                            '%title%' => $version['gameTitle'],
                            '%platform%' => $version['platformName'],
                        ]
                    ),
                'version' => $version,
            ]
        );
    }

    #[Route('/games/filtered/{filter<\w+>}', name: 'versions_filtered_list', methods: ['GET'])]
    public function filteredList(string $filter): Response
    {
        if (false === \array_key_exists($filter, VersionService::FILTERS)) {
            throw new NotFoundHttpException();
        }

        $data = $this->service->getFilteredList($filter);

        return $this->render(
            'version/standard-list.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        VersionService::FILTERS[$filter]['title'],
                        ['%count%' => $data['totalResultCount']]
                    ),
                'screenSubTitle' => $this->translator
                    ->trans('have_copy_for_x_of_them', ['%count%' => $data['ownedCount']]),
                'screenDescription' => $this->translator
                    ->trans(VersionService::FILTERS[$filter]['description']),
                'versions' => $data['result'],
            ]
        );
    }

    #[Route('/game/random/{filter<\w+>}', name: 'version_random', methods: ['GET'])]
    public function getRandom(string $filter): Response
    {
        /** @var array{result: list<array<string, scalar>>, totalResultCount: int} $result */
        $result = $this->service->getRandom($filter);

        if (0 === $result['totalResultCount']) {
            return $this->render(
                'general/no-result.html.twig',
                [
                    'screenTitle' => $this->translator->trans('no_result'),
                ]
            );
        }

        /** @var array<string, scalar> $version */
        $version = $result['result'][0];

        return $this->render(
            'version/details.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        'game.version_details',
                        [
                            '%title%' => $version['gameTitle'],
                            '%platform%' => $version['platformName'],
                        ]
                    ),
                'version' => $version,
            ]
        );
    }

    #[Route('/game/with-priority/{filter<\w+>}', name: 'versions_with_priority', methods: ['GET'])]
    public function getListWithPriority(string $filter): Response
    {
        if (false === \array_key_exists($filter, VersionService::FILTERS_WITH_PRIORITY)) {
            throw new NotFoundHttpException();
        }

        $data = $this->service->getFilteredListWithPrio($filter);

        return $this->render(
            'version/list-with-priority.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        VersionService::FILTERS_WITH_PRIORITY[$filter]['title'],
                        ['%count%' => $data['totalResultCount']]
                    ),
                'screenSubTitle' => $this->translator
                    ->trans('have_copy_for_x_of_them', ['%count%' => $data['ownedCount']]),
                'screenDescription' => $this->translator
                    ->trans(VersionService::FILTERS_WITH_PRIORITY[$filter]['description']),
                'data' => $data,
            ]
        );
    }

    #[Route('/versions/search', name: 'version_search', methods: ['POST'])]
    public function search(Request $request): Response
    {
        $query = \trim($request->request->getString('query'));
        $data = '' !== $query ? $this->service->search($query) : ['result' => [], 'totalResultCount' => 0];

        $params = [
            'screenTitle' => $this->translator
                ->trans(
                    'search_results',
                    ['%count%' => $data['totalResultCount']]
                ),
            'screenSubTitle' => $this->translator->trans(
                'search_results_subtitle',
                ['%query%' => $query]
            ),
            'versions' => $data['result'],
        ];

        if ($data['totalResultCount'] > 0) {
            $params['screenDescription'] = $this->translator
                ->trans('have_copy_for_x_of_them', ['%count%' => $data['ownedCount']]);
        }

        return $this->render(
            'version/standard-list.html.twig',
            $params
        );
    }

    #[Route('/version/add', name: 'add_version', methods: ['GET', 'POST']), IsGranted('ROLE_USER')]
    public function add(Request $request): Response
    {
        if ('GET' === $request->getMethod()) {
            return $this->render(
                'version/form.html.twig',
                [
                    'screenTitle' => $this->translator->trans('menu.add_version'),
                    'games' => $this->gameService->getList()['result'],
                    'platforms' => $this->platformService->getList()['result'],
                    'selectedPlatform' => $request->query->get('platform', 0),
                    'selectedGame' => $request->query->get('game', 0),
                ]
            );
        }

        if (false === $this->isCsrfTokenValid('add_version', $request->request->getString('_csrf_token'))) {
            $this->addFlash('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('add_version');
        }

        $payload = $request->request->all();
        unset($payload['_csrf_token']);

        $id = $this->service->add($payload)['id'];

        return $this->redirectToRoute('version_details', ['id' => $id]);
    }

    #[Route('/version/edit/{id<\d+>}', name: 'edit_version', methods: ['GET', 'POST']), IsGranted('ROLE_USER')]
    public function edit(Request $request, int $id): Response
    {
        $version = $this->service->getById($id);

        if ('GET' === $request->getMethod()) {
            return $this->render(
                'version/form.html.twig',
                [
                    'screenTitle' => $this->translator->trans('menu.edit_version'),
                    'games' => $this->gameService->getList()['result'],
                    'platforms' => $this->platformService->getList()['result'],
                    'selectedPlatform' => $version['platformId'],
                    'selectedGame' => $version['gameId'],
                    'version' => $version,
                ]
            );
        }

        if (false === $this->isCsrfTokenValid('add_version', $request->request->getString('_csrf_token'))) {
            $this->addFlash('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('edit_version', ['id' => $id]);
        }

        $payload = $request->request->all();
        unset($payload['_csrf_token']);

        $this->service->update($id, $payload);

        return $this->redirectToRoute('version_details', ['id' => $id]);
    }

    #[Route('/version/delete/{id<\d+>}', name: 'delete_version', methods: ['POST']), IsGranted('ROLE_USER')]
    public function delete(Request $request, int $id): Response
    {
        if (false === $this->isCsrfTokenValid('delete_version', $request->request->getString('_csrf_token'))) {
            $this->addFlash('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('version_details', ['id' => $id]);
        }

        try {
            $this->service->delete($id);
            $this->addFlash('alert', 'entry_deleted_with_success');
        } catch (GenericApiException $exception) {
            if (404 === $exception->getCode()) {
                // Ignore, not a problem because someone might have done it
            } elseif (400 === $exception->getCode() && 9 === $exception->getApiReturnCode()) {
                $this->addFlash('alert', 'version_has_children');

                return $this->redirectToRoute('version_details', ['id' => $id]);
            }
        }

        return $this->redirectToRoute('games_list');
    }
}
