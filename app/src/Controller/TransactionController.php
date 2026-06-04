<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\GenericApiException;
use App\Service\CopyService;
use App\Service\TransactionService;
use App\Service\VersionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class TransactionController extends AbstractController
{
    public function __construct(
        private readonly TransactionService $service,
        private readonly TranslatorInterface $translator,
        private readonly VersionService $versionService,
        private readonly CopyService $copyService,
    ) {
    }

    #[Route('/transactions', name: 'transaction_list', methods: ['GET'])]
    public function getList(): Response
    {
        $data = $this->service->getList();

        return $this->render(
            'transaction/list.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        'transactions_title',
                        ['%count%' => $data['totalResultCount']]
                    ),
                'screenDescription' => $this->translator->trans('transactions_description'),
                'transactions' => $data['transactions'],
                'gamesBoughtChartData' => \json_encode($data['gamesBoughtChartData']),
                'copiesDistributionAmongPlatformsStats' => \json_encode($data['copiesDistributionAmongPlatformsStats']),
            ]
        );
    }

    #[Route('/transaction/add', name: 'add_transaction_form', methods: ['GET']), IsGranted('ROLE_USER')]
    public function addForm(Request $request): Response
    {
        $versionId = (int) $request->query->get('version');
        $copyId = (int) $request->query->get('copy');

        if (0 === $versionId && 0 === $copyId) {
            $this->addFlash(
                'alert',
                'transaction.no_version_id_and_no_copy_id'
            );

            return $this->redirectToRoute('homepage');
        }

        $version = null;
        if (0 !== $versionId) {
            $version = $this->versionService->getById($versionId);
        }

        $copy = null;
        if (0 !== $copyId) {
            $copy = $this->copyService->getById($copyId);
        }

        return $this->render(
            'transaction/form.html.twig',
            [
                'screenTitle' => $this->translator->trans('menu.add_transaction'),
                'version' => $version,
                'copy' => $copy,
                'versionId' => $versionId,
            ]
        );
    }

    #[Route('/transaction/add', name: 'add_transaction_submit', methods: ['POST']), IsGranted('ROLE_USER')]
    public function addSubmit(Request $request): Response
    {
        $payload = $request->request->all();
        $versionId = $payload['versionId'] ?? 0; // Safety

        if (false === $this->isCsrfTokenValid('add_transaction', $request->request->getString('_csrf_token'))) {
            $this->addFlash('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('copies_per_version', ['versionId' => $versionId]);
        }

        unset($payload['_csrf_token']);

        $this->service->add($payload);

        return $this->redirectToRoute('transaction_list');
    }

    #[Route('/transaction/delete/{id<\d+>}', name: 'delete_transaction', methods: ['POST']), IsGranted('ROLE_USER')]
    public function delete(Request $request, int $id): Response
    {
        if (false === $this->isCsrfTokenValid('delete_transaction', $request->request->getString('_csrf_token'))) {
            $this->addFlash('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('transaction_list');
        }

        try {
            $this->service->delete($id);
            $this->addFlash('alert', 'entry_deleted_with_success');
        } catch (GenericApiException $exception) {
            if (Response::HTTP_NOT_FOUND === $exception->getCode()) {
                // Ignore, not a problem because someone might have done it
            }
        }

        return $this->redirectToRoute('transaction_list');
    }
}
