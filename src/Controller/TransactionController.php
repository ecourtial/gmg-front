<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\GenericApiException;
use App\Service\CopyService;
use App\Service\TransactionService;
use App\Service\VersionService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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

    #[Route('/transactions', methods: ['GET'], name: 'transaction_list')]
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
            ]
        );
    }

    #[Route('/transaction/add', methods: ['GET'], name: 'add_transaction_form'), IsGranted('ROLE_USER')]
    public function addForm(Request $request): Response
    {
        $versionId = (int)$request->get('version');
        $copyId = (int)$request->get('copy');

        if ($versionId === 0 && $copyId === 0) {
            $request->getSession()->getFlashBag()->add(
                'alert',
                'transaction.no_version_id_and_no_copy_id'
            );

            return $this->redirectToRoute('homepage');
        }

        $version = null;
        if ($versionId !== 0) {
            $version = $this->versionService->getById($versionId);
        }

        $copy = null;
        if ($copyId !== 0) {
            $copy = $this->copyService->getById($copyId);
        }

        return $this->render(
            'transaction/form.html.twig',
            [
                'screenTitle' => $this->translator->trans('menu.add_transaction'),
                'version' => $version,
                'copy' => $copy,
                'versionId' => $versionId
            ]
        );
    }

    #[Route('/transaction/add', methods: ['POST'], name: 'add_transaction_submit'), IsGranted('ROLE_USER')]
    public function addSubmit(Request $request): Response
    {
        $payload = $request->request->all();
        $versionId = $payload['versionId'] ?? 0; // Safety

        if (false === $this->isCsrfTokenValid('add_transaction', $request->get('_csrf_token'))) {
            $request->getSession()->getFlashBag()->add('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('copies_per_version', ['versionId' => $versionId]);
        }

        unset($payload['_csrf_token']);

        $this->service->add($payload);

        return $this->redirectToRoute('transaction_list');
    }

    #[Route('/transaction/delete/{id<\d+>}', methods: ['POST'], name: 'delete_transaction'), IsGranted('ROLE_USER')]
    public function delete(Request $request, int $id): Response
    {
        if (false === $this->isCsrfTokenValid('delete_transaction', $request->get('_csrf_token'))) {
            $request->getSession()->getFlashBag()->add('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('transaction_list');
        }

        try {
            $this->service->delete($id);
            $request->getSession()->getFlashBag()->add('alert', 'entry_deleted_with_success');
        } catch (GenericApiException $exception) {
            if ($exception->getCode() === 404) {
                // Ignore, not a problem because someone might have done it
            }
        }

        return $this->redirectToRoute('transaction_list');
    }
}
