<?php

declare(strict_types=1);

namespace App\Controller;

use App\Api\Enum\ApiResponseCode;
use App\Entity\Enum\MagazineIssueCopyType;
use App\Exception\GenericApiException;
use App\ResourceService\MagazineIssueCopyService;
use App\ResourceService\MagazineIssueService;
use App\ResourceService\MagazineService;
use App\Twig\ToolsExtension;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class MagazineIssueCopyController extends AbstractController
{
    public function __construct(
        private readonly MagazineService $magazineService,
        private readonly MagazineIssueService $magazineIssueService,
        private readonly MagazineIssueCopyService $magazineIssueCopyService,
        private readonly TranslatorInterface $translator,
        private readonly ToolsExtension $toolsExtension,
    ) {
    }

    #[Route('/magazine-issue-copy/add/{issueId<\d+>}', name: 'add_magazine_issue_copy', methods: ['GET', 'POST']), IsGranted('ROLE_USER')]
    public function add(Request $request, int $issueId): Response
    {
        if ('GET' === $request->getMethod()) {
            $issue = $this->magazineIssueService->getById($issueId);
            $magazine = $this->magazineService->getById($issue->magazineId);

            return $this->render(
                'magazine-issue-copy/form.html.twig',
                [
                    'screenTitle' => $this->translator->trans('magazine_add_issue_copy'),
                    'screenSubTitle' => $magazine->title.' - Issue #'.$issue->issueNumber.' ('.$this->toolsExtension->getMonthLabel($issue->month).' '.$issue->year.')',
                    'issueId' => $issueId,
                    'copyTypes' => MagazineIssueCopyType::cases(),
                ]
            );
        }

        if (false === $this->isCsrfTokenValid('add_magazine_issue_copy', $request->request->getString('_csrf_token'))) {
            $this->addFlash('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('add_magazine_issue_copy', ['issueId' => $issueId]);
        }

        $payload = $request->request->all();
        unset($payload['_csrf_token']);

        $this->magazineIssueCopyService->add($payload);

        return $this->redirectToRoute('magazine_issue_details', ['issueId' => $issueId]);
    }

    #[Route('/magazine-issue-copy/delete/{id<\d+>}', name: 'delete_magazine_issue_copy', methods: ['POST']), IsGranted('ROLE_USER')]
    public function delete(Request $request, int $id): Response
    {
        if (false === $this->isCsrfTokenValid('delete_magazine_issue_copy', $request->request->getString('_csrf_token'))) {
            $this->addFlash('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('magazines_list');
        }

        try {
            $copy = $this->magazineIssueCopyService->getById($id);
            $this->magazineIssueCopyService->delete($id);
            $this->addFlash('alert', 'entry_deleted_with_success');

            return $this->redirectToRoute('magazine_issue_details', ['issueId' => $copy->magazineIssueId]);
        } catch (GenericApiException $exception) {
            if (Response::HTTP_NOT_FOUND === $exception->getCode()) {
                // Ignore, not a problem because someone might have done it in the meantime.
            } elseif (Response::HTTP_BAD_REQUEST === $exception->getCode() && ApiResponseCode::RESOURCE_HAS_LINKED_RESOURCES->value === $exception->getApiReturnCode()) {
                $this->addFlash('alert', 'magazine_issue_has_linked_resources');
            }
        }

        return $this->redirectToRoute('magazines_list');
    }
}
