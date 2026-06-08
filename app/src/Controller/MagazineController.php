<?php
declare(strict_types=1);

namespace App\Controller;

use App\Api\Enum\ApiResponseCode;
use App\Exception\GenericApiException;
use App\Service\MagazineIssueService;
use App\Service\MagazineService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class MagazineController extends AbstractController
{
    public function __construct(
        private readonly MagazineService $magazineService,
        private readonly MagazineIssueService $magazineIssueService,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('/magazines', name: 'magazines_list', methods: ['GET'])]
    public function list(): Response
    {
        $data = $this->magazineService->getList();

        return $this->render(
            'magazine/list.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        'magazines_list_title',
                        ['%count%' => $data['totalResultCount']]
                    ),
                'magazines' => $data['result'],
            ]
        );
    }

    #[Route('/magazine/{id<\d+>}', name: 'magazine_details', methods: ['GET'])]
    public function get(int $id): Response
    {
        $data = $this->magazineIssueService->getByMagazine($id);
        /** @var array{result: mixed, totalResultCount: mixed, ownedCount: mixed} $versions */
        $magazine = $this->magazineService->getById($id);

        return $this->render(
            'magazine/details.html.twig',
            [
                'screenTitle' => $this->translator
                    ->trans(
                        'magazine_issues_list',
                        ['%title%' => $magazine['title']]
                    ),
                'screenSubTitle' => $this->isGranted('ROLE_USER') ? $magazine['notes'] : '',
                'issues' => $data['result'],
                'magazine' => $magazine,
            ]
        );
    }

    #[Route('/magazine/add', name: 'add_magazine', methods: ['GET', 'POST']), IsGranted('ROLE_USER')]
    public function add(Request $request): Response
    {
        if ('GET' === $request->getMethod()) {
            return $this->render(
                'magazine/form.html.twig',
                ['screenTitle' => $this->translator->trans('menu.add_magazine')]
            );
        }

        if (false === $this->isCsrfTokenValid('add_magazine', $request->request->getString('_csrf_token'))) {
            $this->addFlash('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('add_magazine');
        }

        $payload = $request->request->all();

        unset($payload['_csrf_token']);

        $id = $this->magazineService->add($payload)['id'];

        return $this->redirectToRoute('magazine_details', ['id' => $id]);
    }

    #[Route('/magazine/edit/{id<\d+>}', name: 'edit_magazine', methods: ['GET', 'POST']), IsGranted('ROLE_USER')]
    public function edit(Request $request, int $id): Response
    {
        $magazine = $this->magazineService->getById($id);

        if ('GET' === $request->getMethod()) {
            return $this->render(
                'magazine/form.html.twig',
                [
                    'screenTitle' => $this->translator->trans('menu.edit_magazine', ['%title%' => $magazine['title']]),
                    'magazine' => $magazine,
                ]
            );
        }

        if (false === $this->isCsrfTokenValid('add_magazine', $request->request->getString('_csrf_token'))) {
            $this->addFlash('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('edit_magazine', ['id' => $id]);
        }

        $payload = $request->request->all();
        unset($payload['_csrf_token']);

        $this->magazineService->update($id, $payload);

        return $this->redirectToRoute('magazine_details', ['id' => $id]);
    }

    #[Route('/magazine/delete/{id<\d+>}', name: 'delete_magazine', methods: ['POST']), IsGranted('ROLE_USER')]
    public function delete(Request $request, int $id): Response
    {
        if (false === $this->isCsrfTokenValid('delete_magazine', $request->request->getString('_csrf_token'))) {
            $this->addFlash('alert', 'see.invalid_csrf_token');

            return $this->redirectToRoute('magazine_details', ['id' => $id]);
        }

        try {
            $this->magazineService->delete($id);
            $this->addFlash('alert', 'entry_deleted_with_success');
        } catch (GenericApiException $exception) {
            if (Response::HTTP_NOT_FOUND === $exception->getCode()) {
                // Ignore, not a problem because someone might have done it in the meantime.
            } elseif (Response::HTTP_BAD_REQUEST === $exception->getCode() && ApiResponseCode::RESOURCE_HAS_LINKED_RESOURCES->value === $exception->getApiReturnCode()) {
                $this->addFlash('alert', 'magazine_has_issues');

                return $this->redirectToRoute('magazine_details', ['id' => $id]);
            }
        }

        return $this->redirectToRoute('magazines_list');
    }
}
