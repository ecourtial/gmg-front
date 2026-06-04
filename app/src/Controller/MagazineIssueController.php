<?php
declare(strict_types=1);

namespace App\Controller;

use App\Api\Enum\ApiResponseCode;
use App\Exception\GenericApiException;
use App\Service\GameMagazineMentionService;
use App\Service\MagazineIssueCopyService;
use App\Service\MagazineIssueService;
use App\Service\MagazineService;
use App\Service\VersionService;
use App\Twig\ToolsExtension;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class MagazineIssueController extends AbstractController
{
    public function __construct(
        private readonly MagazineService $magazineService,
        private readonly MagazineIssueService $magazineIssueService,
        private readonly GameMagazineMentionService $gameMagazineMentionService,
        private readonly VersionService $versionService,
        private readonly MagazineIssueCopyService $magazineIssueCopyService,
        private readonly ToolsExtension $toolsExtension,
        private readonly TranslatorInterface $translator,
    ) {
    }
    #[Route('/magazine-issue/{issueId<\d+>}', name: 'magazine_issue_details', methods: ['GET'])]
    public function get(int $issueId): Response
    {
        $issue = $this->magazineIssueService->getById($issueId);
        /** @var array{result: mixed, totalResultCount: mixed, ownedCount: mixed} $versions */
        $magazine = $this->magazineService->getById($issue['magazineId']);
        $gameMentions = $this->gameMagazineMentionService->getByIssueId($issueId)['result'];

        /** @todo refactorize as we could perform only one call to the API using filterBy[] */
        $gamesVersions = [];
        foreach ($gameMentions as $gameMention) {
            $gameVersionId = $gameMention['gameVersionId'];
            if (false === array_key_exists($gameVersionId, $gamesVersions)) {
                $gamesVersions[$gameVersionId] = $this->versionService->getById($gameVersionId);
            }
        }

        $sortedMentions = $this->magazineIssueService->formatMentions($gameMentions, $gamesVersions);
        $copies = $this->magazineIssueCopyService->getByIssueId($issueId)['result'];

        return $this->render(
            'magazine-issue/details.html.twig',
            [
                'screenTitle' => $magazine['title'],
                'screenSubTitle' => $this->translator->trans('issue').' #'.$issue['issueNumber'].' ('.$this->toolsExtension->getMonthLabel($issue['month']).' '.$issue['year'].')',
                'issue' => $issue,
                'copies' => $copies,
                'mentions' => $sortedMentions,
            ]
        );
    }

    #[Route('/magazine-issue/add', name: 'add_magazine_issue', methods: ['GET', 'POST']), IsGranted('ROLE_USER')]
    public function add(Request $request): Response
    {
        if ('GET' === $request->getMethod()) {
            return $this->render(
                'magazine-issue/form.html.twig',
                [
                    'screenTitle' => $this->translator->trans('menu.magazine_add_issue'),
                    'magazines' => $this->magazineService->getList()['result'],
                    'selectedMagazine' => $request->query->get('selectedMagazine', 0),
                ]
            );
        }

        if (false === $this->isCsrfTokenValid('add_magazine_issue', $request->request->getString('_csrf_token'))) {
            $this->addFlash('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('add_magazine_issue');
        }

        $payload = $request->request->all();
        unset($payload['_csrf_token']);

        $issue = $this->magazineIssueService->add($payload);

        return $this->redirectToRoute('magazine_issue_details', ['issueId' => $issue['id']]);
    }

    #[Route('/magazine-issue/edit/{id<\d+>}', name: 'edit_magazine_issue', methods: ['GET', 'POST']), IsGranted('ROLE_USER')]
    public function edit(Request $request, int $id): Response
    {
        $issue = $this->magazineIssueService->getById($id);

        if ('GET' === $request->getMethod()) {
            return $this->render(
                'magazine-issue/form.html.twig',
                [
                    'screenTitle' => $this->translator->trans('magazine_edit_issue'),
                    'screenSubTitle' => $this->translator->trans('issue').' #'.$issue['issueNumber'].' ('.$this->toolsExtension->getMonthLabel($issue['month']).' '.$issue['year'].')',
                    'magazines' => $this->magazineService->getList()['result'],
                    'selectedMagazine' => $issue['magazineId'],
                    'issue' => $issue,
                ]
            );
        }

        if (false === $this->isCsrfTokenValid('add_magazine_issue', $request->request->getString('_csrf_token'))) {
            $this->addFlash('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('edit_magazine_issue', ['id' => $id]);
        }

        $payload = $request->request->all();
        unset($payload['_csrf_token']);

        $this->magazineIssueService->update($id, $payload);

        return $this->redirectToRoute('magazine_issue_details', ['issueId' => $id]);
    }

    #[Route('/magazine-issue/delete/{id<\d+>}', name: 'delete_magazine_issue', methods: ['POST']), IsGranted('ROLE_USER')]
    public function delete(Request $request, int $id): Response
    {
        if (false === $this->isCsrfTokenValid('delete_magazine_issue', $request->request->getString('_csrf_token'))) {
            $this->addFlash('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('magazine_issue_details', ['id' => $id]);
        }

        try {
            $issue = $this->magazineIssueService->getById($id);
            $this->magazineIssueService->delete($id);
            $this->addFlash('alert', 'entry_deleted_with_success');

            return $this->redirectToRoute('magazine_details', ['id' => $issue['magazineId']]);
        } catch (GenericApiException $exception) {
            if (Response::HTTP_NOT_FOUND === $exception->getCode()) {
                // Ignore, not a problem because someone might have done it in the meantime.
            } elseif (Response::HTTP_BAD_REQUEST === $exception->getCode() && ApiResponseCode::RESOURCE_HAS_LINKED_RESOURCES->value === $exception->getApiReturnCode()) {
                $this->addFlash('alert', 'magazine_issue_has_linked_resources');

                return $this->redirectToRoute('magazine_issue_details', ['issueId' => $id]);
            }
        }

        return $this->redirectToRoute('magazines_list', ['id' => $id]);
    }
}
