<?php

namespace App\Controller;

use App\Entity\InterestPoint;
use App\Entity\Map;
use App\Repository\InterestPointRepository;
use App\Repository\InterestPointTypeRepository;
use App\Repository\MapRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/map')]
class MapController extends AbstractController
{
    public function __construct(
        private MapRepository $mapRepository,
        private InterestPointRepository $interestPointRepository,
        private InterestPointTypeRepository $typeRepository,
    ) {
    }

    /**
     * Main map view page
     */
    #[Route('', name: 'map_index', methods: ['GET'])]
    public function index(): Response
    {
        $maps = $this->mapRepository->findAllWithPointCounts();
        $defaultMap = $this->mapRepository->findDefault();

        return $this->render('map/index.html.twig', [
            'maps' => $maps,
            'defaultMap' => $defaultMap,
        ]);
    }

    /**
     * View a specific map
     */
    #[Route('/{id}', name: 'map_view', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function view(Map $map): Response
    {
        $maps = $this->mapRepository->findAllWithPointCounts();
        $points = $this->interestPointRepository->findByMap($map);
        $types = $this->typeRepository->findAllOrdered();

        return $this->render('map/view.html.twig', [
            'map' => $map,
            'maps' => $maps,
            'points' => $points,
            'types' => $types,
        ]);
    }

    /**
     * View details of a specific place/interest point
     */
    #[Route('/place/{id}', name: 'map_place_detail', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function placeDetail(InterestPoint $point): Response
    {
        return $this->render('map/place_detail.html.twig', [
            'point' => $point,
        ]);
    }

    /**
     * API: Get all maps
     */
    #[Route('/api/maps', name: 'map_api_maps', methods: ['GET'])]
    public function apiGetMaps(): JsonResponse
    {
        $maps = $this->mapRepository->findAll();
        $result = [];

        foreach ($maps as $map) {
            $result[] = [
                'id' => $map->getId(),
                'name' => $map->getName(),
                'image' => $map->getImageFile(),
                'description' => $map->getDescription(),
                'pointCount' => $map->getInterestPoints()->count(),
            ];
        }

        return $this->json(['success' => true, 'maps' => $result]);
    }

    /**
     * API: Get points for a specific map
     */
    #[Route('/api/points/{id}', name: 'map_api_points', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function apiGetPoints(Map $map): JsonResponse
    {
        $points = $this->interestPointRepository->findByMapForApi($map->getId());

        return $this->json([
            'success' => true,
            'points' => $points,
            'count' => count($points),
        ]);
    }

    /**
     * API: Get all point types
     */
    #[Route('/api/types', name: 'map_api_types', methods: ['GET'])]
    public function apiGetTypes(): JsonResponse
    {
        $types = $this->typeRepository->findAllOrdered();
        $result = [];

        foreach ($types as $type) {
            $result[] = [
                'id' => $type->getId(),
                'name' => $type->getName(),
                'color' => $type->getColor(),
                'icon' => $type->getIcon(),
            ];
        }

        return $this->json(['success' => true, 'types' => $result]);
    }

    /**
     * API: Check if a point name is a duplicate
     */
    #[Route('/api/check-duplicate', name: 'map_api_check_duplicate', methods: ['POST'])]
    public function apiCheckDuplicate(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $name = $data['name'] ?? '';
        $excludeId = $data['exclude_id'] ?? null;

        if (empty($name)) {
            return $this->json(['success' => false, 'message' => 'Le nom est requis']);
        }

        $existing = $this->interestPointRepository->findByName($name, $excludeId);

        return $this->json([
            'success' => true,
            'isDuplicate' => $existing !== null,
            'message' => $existing ? 'Un lieu avec ce nom existe déjà' : null,
        ]);
    }
}
