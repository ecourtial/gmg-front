<?php

declare(strict_types=1);

namespace App\Controller;

use App\Api\Enum\ApiResponseCode;
use App\Api\ResourceCollectionResponseDto;
use App\Entity\Dto\Specific\VersionsDataDto;
use App\Exception\GenericApiException;
use App\ResourceService\CopyService;
use App\ResourceService\GameMagazineMentionService;
use App\ResourceService\GameService;
use App\ResourceService\MagazineIssueService;
use App\ResourceService\MagazineService;
use App\ResourceService\NoteService;
use App\ResourceService\PlatformService;
use App\ResourceService\TransactionService;
use App\ResourceService\VersionService;
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
        private readonly GameMagazineMentionService $gameMagazineMentionService,
        private readonly MagazineService $magazineService,
        private readonly MagazineIssueService $magazineIssueService,
        private readonly TransactionService $transactionService,
        private readonly NoteService $noteService,
        private readonly CopyService $copyService,
    ) {
    }

    #[Route('/version/{id<\d+>}', name: 'version_details', methods: ['GET'])]
    public function versionDetails(int $id): Response
    {
        $version = $this->service->getById($id);
        $transactions = $this->transactionService->getTransactionsData($id);
        [$magazines, $issues, $mentions] = $this->prepareMentions($id);
        $notes = $this->noteService->getList($id);

        return $this->render(
            'version/details.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        'game.version_details',
                        [
                            '%title%' => $version->gameTitle,
                            '%platform%' => $version->platformName,
                        ]
                    ),
                'screenSubTitle' => $this->isGranted('ROLE_USER') ? $version->comments : '',
                'version' => $version,
                'mentionsByType' => $this->service->formatMentions($magazines, $issues, $mentions),
                'transactionsCount' => $transactions['totalResultCount'],
                'notes' => $notes->result,
            ]
        );
    }

    #[Route('/versions/filtered/{filter<\w+>}', name: 'versions_filtered_list', methods: ['GET'])]
    public function filteredList(string $filter): Response
    {
        if (false === \array_key_exists($filter, VersionService::FILTERS)) {
            throw new NotFoundHttpException();
        }

        $needCopies = VersionService::FILTERS[$filter]['filter_from_copies'] ?? false;
        $copies = null;

        if (true === $needCopies) {
            $filterValue = strval(VersionService::FILTERS[$filter]['attribute_value'] ?? '1');
            $filterAttribute = VersionService::FILTERS[$filter]['attribute'];
            $copies = $this->copyService->getList($filterAttribute, $filterValue);
        }

        $data = $this->service->getFilteredList($filter, copies: $copies);

        /**
         * Ugly! @TODO implement that on API side please.
         * On top of that we could have used a simple getList() from the service.
         * But at least it reminds us that we need to improve filters on the API side.
         */
        if ($filter === VersionService::WITH_COMMENTS_FILTER) {
            $results = $data->versions->result;
            $ownedCount = $data->ownedCount;

            foreach ($data->versions->result as $key => $item) {
                if (null === $item->comments
                    || trim($item->comments) === '') {
                    if (0 < $item->copyCount) {
                        $ownedCount--;
                    }
                    unset($results[$key]);
                }
            }

            $data = new VersionsDataDto(
                new ResourceCollectionResponseDto(
                    $data->versions->resultCount,
                    $data->versions->totalResultCount,
                    $data->versions->page,
                    $data->versions->totalPageCount,
                    $results
                ),
                $ownedCount
            );
        }

        return $this->render(
            'version/standard-list.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        VersionService::FILTERS[$filter]['title'],
                        ['%count%' => $data->versions->totalResultCount],
                    ),
                'screenSubTitle' => $this->translator
                    ->trans('have_copy_for_x_of_them', ['%count%' => $data->ownedCount]),
                'screenDescription' => $this->translator
                    ->trans(VersionService::FILTERS[$filter]['description']),
                'versions' => $data->versions->result,
            ]
        );
    }

    #[Route('/version/random/{filter<\w+>}', name: 'version_random', methods: ['GET'])]
    public function getRandom(string $filter): Response
    {
        $result = $this->service->getRandom($filter);

        if (0 === $result->totalResultCount) {
            return $this->render(
                'general/no-result.html.twig',
                [
                    'screenTitle' => $this->translator->trans('no_result'),
                ]
            );
        }

        $version = $result->result[0];
        [$magazines, $issues, $mentions] = $this->prepareMentions($version->id);
        $transactions = $this->transactionService->getTransactionsData($version->id);
        $notes = $this->noteService->getList($version->id);

        return $this->render(
            'version/details.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        'game.version_details',
                        [
                            '%title%' => $version->gameTitle,
                            '%platform%' => $version->platformName,
                        ]
                    ),
                'version' => $version,
                'transactionsCount' => $transactions['totalResultCount'],
                'mentionsByType' => $this->service->formatMentions($magazines, $issues, $mentions),
                'notes' => $notes['result'],
            ]
        );
    }

    #[Route('/version/with-priority/{filter<\w+>}', name: 'versions_with_priority', methods: ['GET'])]
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
                        ['%count%' => $data->totalResultCount]
                    ),
                'screenSubTitle' => $this->translator
                    ->trans('have_copy_for_x_of_them', ['%count%' => $data->ownedCount]),
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
        $data = '' !== $query ? $this->service->search($query) : new VersionsDataDto(new ResourceCollectionResponseDto(), 0);

        $params = [
            'screenTitle' => $this->translator
                ->trans(
                    'search_results',
                    ['%count%' => $data->versions->totalResultCount],
                ),
            'screenSubTitle' => $this->translator->trans(
                'search_results_subtitle',
                ['%query%' => $query]
            ),
            'versions' => $data->versions->result,
        ];

        if ($data->versions->totalResultCount > 0) {
            $params['screenDescription'] = $this->translator
                ->trans('have_copy_for_x_of_them', ['%count%' => $data->ownedCount]);
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
                    'games' => $this->gameService->getList()->games->result,
                    'platforms' => $this->platformService->getList()->result,
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

        $id = $this->service->add($payload)->id;

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
                    'games' => $this->gameService->getList()->games->result,
                    'platforms' => $this->platformService->getList()->result,
                    'selectedPlatform' => $version->platformId,
                    'selectedGame' => $version->gameId,
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
            if (Response::HTTP_NOT_FOUND === $exception->getCode()) {
                // Ignore, not a problem because someone might have done it
            } elseif (Response::HTTP_BAD_REQUEST === $exception->getCode() && ApiResponseCode::RESOURCE_HAS_LINKED_RESOURCES->value === $exception->getApiReturnCode()) {
                $this->addFlash('alert', 'version_has_children');

                return $this->redirectToRoute('version_details', ['id' => $id]);
            }
        }

        return $this->redirectToRoute('games_list');
    }

    private function prepareMentions(int $versionId): array
    {
        $mentions = $this->gameMagazineMentionService->getByVersionId($versionId)->result;
        $issues = [];
        $magazines = [];

        /** @todo refactorize as we could perform only one call (per type) to the API using filterBy[] */
        foreach ($mentions as $mention) {
            $magazineIssueId = $mention->magazineIssueId;
            if (false === array_key_exists($magazineIssueId, $issues)) {
                $issue = $this->magazineIssueService->getById($magazineIssueId);
                $issues[$magazineIssueId] = $issue;

                $magazineId = $issue->magazineId;
                if (false === array_key_exists($magazineId, $magazines)) {
                    $magazines[$magazineId] = $this->magazineService->getById($magazineId);
                }
            }
        }

        return [$magazines, $issues, $mentions];
    }
}
