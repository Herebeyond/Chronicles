<?php

namespace App\Controller;

use App\Service\HubSearchService;
use App\Service\NavigationPageCardService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class HubController extends AbstractController
{
    public function __construct(
        private readonly HubSearchService $hubSearchService,
        private readonly NavigationPageCardService $navigationPageCardService,
    ) {
    }

    #[Route('/hub', name: 'hub_index')]
    public function index(): Response
    {
        $pages = $this->navigationPageCardService->hydratePages($this->hubSearchService->getAvailablePages());
        $enabledPages = array_map(static fn(array $page): string => $page['key'], $pages);

        return $this->render('hub/index.html.twig', [
            'page_title' => 'Navigation',
            'pages' => $pages,
            'enabled_pages' => $enabledPages,
        ]);
    }

    #[Route('/hub/go', name: 'hub_go')]
    public function go(Request $request): RedirectResponse
    {
        $pageQuery = (string) $request->query->get('page', '');
        $routeName = $this->hubSearchService->resolvePageRouteFromQuery($pageQuery);

        if (!$routeName) {
            $this->addFlash('error', 'Unknown page type. Try Beings or Characters.');
            return $this->redirectToRoute('hub_index');
        }

        return $this->redirectToRoute($routeName);
    }

    #[Route('/admin/navigation/page-card/{pageKey}', name: 'admin_navigation_page_card_update', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function updatePageCard(string $pageKey, Request $request): RedirectResponse
    {
        $availablePageKeys = array_map(
            static fn(array $page): string => $page['key'],
            $this->hubSearchService->getAvailablePages()
        );

        if (!in_array($pageKey, $availablePageKeys, true)) {
            $this->addFlash('error', 'Unknown navigation page key.');
            return $this->redirectToRoute('hub_index');
        }

        $token = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid('navigation_card_' . $pageKey, $token)) {
            $this->addFlash('error', 'Invalid security token. Please retry.');
            return $this->redirectToRoute('hub_index');
        }

        $image = $request->files->get('image');
        if ($image !== null && !$image instanceof UploadedFile) {
            $this->addFlash('error', 'Invalid image upload payload.');
            return $this->redirectToRoute('hub_index');
        }

        if ($image instanceof UploadedFile) {
            if ($image->getSize() !== null && $image->getSize() > 5 * 1024 * 1024) {
                $this->addFlash('error', 'Image is too large. Maximum allowed size is 5MB.');
                return $this->redirectToRoute('hub_index');
            }

            $mime = (string) $image->getMimeType();
            $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if (!in_array($mime, $allowed, true)) {
                $this->addFlash('error', 'Unsupported image format. Use JPG, PNG, GIF, or WebP.');
                return $this->redirectToRoute('hub_index');
            }
        }

        try {
            $this->navigationPageCardService->updatePage(
                $pageKey,
                $image,
                [
                    'x' => (float) $request->request->get('crop_x', 0),
                    'y' => (float) $request->request->get('crop_y', 0),
                    'size' => (float) $request->request->get('crop_size', 100),
                ]
            );
        } catch (\Throwable $exception) {
            $this->addFlash('error', 'Failed to save page card image settings: ' . $exception->getMessage());
            return $this->redirectToRoute('hub_index');
        }

        $this->addFlash('success', 'Navigation page card updated.');
        return $this->redirectToRoute('hub_index');
    }

    #[Route('/hub/suggestions', name: 'hub_suggestions')]
    public function suggestions(Request $request): JsonResponse
    {
        $query = (string) $request->query->get('q', '');
        $scope = (string) $request->query->get('scope', '');

        $enabledPages = array_values(array_filter(array_map('trim', explode(',', $scope))));
        if ($enabledPages === []) {
            $enabledPages = array_map(
                static fn(array $page): string => $page['key'],
                $this->hubSearchService->getAvailablePages()
            );
        }

        $results = $this->hubSearchService->search($query, $enabledPages, 10);

        $payload = array_map(function (array $result): array {
            return [
                'uid' => $result['uid'],
                'label' => $result['label'],
                'type' => $result['type'],
                'typeLabel' => $result['typeLabel'],
                'depth' => $result['depth'],
                'icon' => $result['icon'],
                'url' => $this->generateUrl($result['routeName'], $result['routeParams']),
            ];
        }, $results);

        return $this->json([
            'query' => $query,
            'count' => count($payload),
            'suggestions' => $payload,
        ]);
    }
}
