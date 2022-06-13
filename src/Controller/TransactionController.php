<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\TransactionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class TransactionController extends AbstractController
{
    public function __construct(
        private readonly TransactionService $service,
        private readonly TranslatorInterface $translator,
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
                'transactions' => $data['transactions']
            ]
        );
    }
}
