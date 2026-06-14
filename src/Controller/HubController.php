<?php

namespace App\Controller;

use App\Service\HubSearchService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HubController extends AbstractController
{
    public function __construct(private readonly HubSearchService $hubSearchService)
    {
    }

    #[Route('/hub', name: 'hub_index')]
    public function index(): Response
    {
        $pages = $this->hubSearchService->getAvailablePages();
        $enabledPages = array_map(static fn(array $page): string => $page['key'], $pages);

        return $this->render('hub/index.html.twig', [
            'page_title' => 'Search Hub',
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
