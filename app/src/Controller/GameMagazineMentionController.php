<?php
declare(strict_types=1);

namespace App\Controller;

use App\Api\Enum\ApiResponseCode;
use App\Entity\Enum\MentionTypeEnum;
use App\Exception\GenericApiException;
use App\Service\GameMagazineMentionService;
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

class GameMagazineMentionController extends AbstractController
{
    public function __construct(
        private readonly VersionService $versionService,
        private readonly GameMagazineMentionService $gameMagazineMentionService,
        private readonly MagazineService $magazineService,
        private readonly MagazineIssueService $magazineIssueService,
        private readonly TranslatorInterface $translator,
        private readonly ToolsExtension $toolsExtension,
    ) {
    }

    #[Route('/game-magazine-mention/add/{issueId<\d+>}', name: 'add_game_magazine_mention', methods: ['GET', 'POST']), IsGranted('ROLE_USER')]
    public function add(Request $request, int $issueId): Response
    {
        if ('GET' === $request->getMethod()) {
            $issue = $this->magazineIssueService->getById($issueId);
            $magazine = $this->magazineService->getById($issue['magazineId']);

            return $this->render(
                'game-magazine-mention/form.html.twig',
                [
                    'screenTitle' => $this->translator->trans('menu.add_mention_in_magazine'),
                    'screenSubTitle' => $magazine['title'] . ' - ' . 'Issue #'.$issue['issueNumber'] . ' ('.$this->toolsExtension->getMonthLabel($issue['month']).' '.$issue['year'].')',
                    'versions' => $this->versionService->getList()['result'],
                    'mentionTypes' => MentionTypeEnum::cases(),
                    'issueId' => $issueId,
                ]
            );
        }

        if (false === $this->isCsrfTokenValid('add_game_magazine_mention', $request->request->getString('_csrf_token'))) {
            $this->addFlash('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('add_game_magazine_mention', ['issueId' => $issueId]);
        }

        $payload = $request->request->all();
        unset($payload['_csrf_token']);

        $this->gameMagazineMentionService->add($payload);

        return $this->redirectToRoute('magazine_issue_details', ['issueId' => $issueId]);
    }

    #[Route('/game-magazine-mention/delete/{id<\d+>}', name: 'delete_game_magazine_mention', methods: ['POST']), IsGranted('ROLE_USER')]
    public function delete(Request $request, int $id): Response
    {
        if (false === $this->isCsrfTokenValid('delete_game_magazine_mention', $request->request->getString('_csrf_token'))) {
            $this->addFlash('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('magazines_list');
        }

        try {
            $mention = $this->gameMagazineMentionService->getById($id);
            $this->gameMagazineMentionService->delete($id);
            $this->addFlash('alert', 'entry_deleted_with_success');

            return $this->redirectToRoute('magazine_issue_details', ['issueId' => $mention['magazineIssueId']]);
        } catch (GenericApiException $exception) {
            if (404 === $exception->getCode()) {
                // Ignore, not a problem because someone might have done it in the meantime.
            } elseif (400 === $exception->getCode() && ApiResponseCode::RESOURCE_HAS_LINKED_RESOURCES->value === $exception->getApiReturnCode()) {
                $this->addFlash('alert', 'magazine_issue_has_linked_resources');
            }
        }

        return $this->redirectToRoute('magazines_list');
    }
}
