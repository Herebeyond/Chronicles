<?php

namespace App\Controller\Admin;

use App\Entity\InterestPoint;
use App\Entity\InterestPointType as InterestPointTypeEntity;
use App\Entity\Map;
use App\Form\InterestPointFormType;
use App\Form\InterestPointTypeType;
use App\Form\MapType;
use App\Repository\InterestPointRepository;
use App\Repository\InterestPointTypeRepository;
use App\Repository\MapRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/maps')]
#[IsGranted('ROLE_ADMIN')]
class MapManagementController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MapRepository $mapRepository,
        private InterestPointRepository $pointRepository,
        private InterestPointTypeRepository $typeRepository,
        private SluggerInterface $slugger,
    ) {
    }

    /**
     * Admin dashboard for map management
     */
    #[Route('', name: 'admin_maps_index', methods: ['GET'])]
    public function index(): Response
    {
        $maps = $this->mapRepository->findAllWithPointCounts();
        $types = $this->typeRepository->findAllWithPointCounts();
        
        return $this->render('admin/maps/index.html.twig', [
            'maps' => $maps,
            'types' => $types,
        ]);
    }

    /**
     * Create a new map
     */
    #[Route('/new', name: 'admin_maps_new', methods: ['GET', 'POST'])]
    public function newMap(Request $request): Response
    {
        $map = new Map();
        $form = $this->createForm(MapType::class, $map);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageUpload')->getData();
            if ($imageFile) {
                $map->setImageFile($this->handleImageUpload($imageFile, 'maps'));
            }

            $this->entityManager->persist($map);
            $this->entityManager->flush();

            $this->addFlash('success', 'La carte a été créée avec succès.');
            return $this->redirectToRoute('admin_maps_index');
        }

        return $this->render('admin/maps/form.html.twig', [
            'form' => $form->createView(),
            'map' => null,
            'title' => 'Nouvelle Carte',
        ]);
    }

    /**
     * Edit an existing map
     */
    #[Route('/{id}/edit', name: 'admin_maps_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function editMap(Request $request, Map $map): Response
    {
        $form = $this->createForm(MapType::class, $map);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageUpload')->getData();
            if ($imageFile) {
                // Delete old image
                $this->deleteOldImage($map->getImageFile(), 'maps');
                $map->setImageFile($this->handleImageUpload($imageFile, 'maps'));
            }

            $map->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();

            $this->addFlash('success', 'La carte a été modifiée avec succès.');
            return $this->redirectToRoute('admin_maps_index');
        }

        return $this->render('admin/maps/form.html.twig', [
            'form' => $form->createView(),
            'map' => $map,
            'title' => 'Modifier la Carte',
        ]);
    }

    /**
     * Delete a map
     */
    #[Route('/{id}/delete', name: 'admin_maps_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function deleteMap(Request $request, Map $map): Response
    {
        if ($this->isCsrfTokenValid('delete' . $map->getId(), $request->request->get('_token'))) {
            // Delete map image
            $this->deleteOldImage($map->getImageFile(), 'maps');

            $this->entityManager->remove($map);
            $this->entityManager->flush();

            $this->addFlash('success', 'La carte a été supprimée avec succès.');
        }

        return $this->redirectToRoute('admin_maps_index');
    }

    /**
     * Interactive map editor
     */
    #[Route('/{id}/editor', name: 'admin_maps_editor', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function editor(Map $map): Response
    {
        $points = $this->pointRepository->findByMap($map);
        $types = $this->typeRepository->findAllOrdered();
        $maps = $this->mapRepository->findAll();

        return $this->render('admin/maps/editor.html.twig', [
            'map' => $map,
            'points' => $points,
            'types' => $types,
            'maps' => $maps,
        ]);
    }

    // ===== Point Type Management =====

    /**
     * Create a new point type
     */
    #[Route('/types/new', name: 'admin_maps_types_new', methods: ['GET', 'POST'])]
    public function newType(Request $request): Response
    {
        $type = new InterestPointTypeEntity();
        $form = $this->createForm(InterestPointTypeType::class, $type);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($type);
            $this->entityManager->flush();

            $this->addFlash('success', 'Le type de lieu a été créé avec succès.');
            return $this->redirectToRoute('admin_maps_index');
        }

        return $this->render('admin/maps/type_form.html.twig', [
            'form' => $form->createView(),
            'type' => null,
            'title' => 'Nouveau Type de Lieu',
        ]);
    }

    /**
     * Edit a point type
     */
    #[Route('/types/{id}/edit', name: 'admin_maps_types_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function editType(Request $request, InterestPointTypeEntity $type): Response
    {
        $form = $this->createForm(InterestPointTypeType::class, $type);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Le type de lieu a été modifié avec succès.');
            return $this->redirectToRoute('admin_maps_index');
        }

        return $this->render('admin/maps/type_form.html.twig', [
            'form' => $form->createView(),
            'type' => $type,
            'title' => 'Modifier le Type de Lieu',
        ]);
    }

    /**
     * Delete a point type
     */
    #[Route('/types/{id}/delete', name: 'admin_maps_types_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function deleteType(Request $request, InterestPointTypeEntity $type): Response
    {
        if ($this->isCsrfTokenValid('delete' . $type->getId(), $request->request->get('_token'))) {
            if ($this->typeRepository->isTypeInUse($type)) {
                $this->addFlash('error', 'Ce type est utilisé par des lieux et ne peut pas être supprimé.');
            } else {
                $this->entityManager->remove($type);
                $this->entityManager->flush();
                $this->addFlash('success', 'Le type de lieu a été supprimé avec succès.');
            }
        }

        return $this->redirectToRoute('admin_maps_index');
    }

    // ===== Interest Point Management =====

    /**
     * Create a new interest point
     */
    #[Route('/points/new', name: 'admin_maps_points_new', methods: ['GET', 'POST'])]
    public function newPoint(Request $request): Response
    {
        $point = new InterestPoint();
        $form = $this->createForm(InterestPointFormType::class, $point);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Persist first to ensure we have the entity ready
            $this->entityManager->persist($point);
            
            $imageFile = $form->get('mainImageUpload')->getData();
            if ($imageFile) {
                $point->setMainImage($this->handlePlaceImageUpload($imageFile, $point, false));
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Le lieu a été créé avec succès.');
            return $this->redirectToRoute('admin_maps_editor', ['id' => $point->getMap()->getId()]);
        }

        return $this->render('admin/maps/point_form.html.twig', [
            'form' => $form->createView(),
            'point' => null,
            'title' => 'Nouveau Lieu',
        ]);
    }

    /**
     * Edit an interest point
     */
    #[Route('/points/{id}/edit', name: 'admin_maps_points_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function editPoint(Request $request, InterestPoint $point): Response
    {
        $form = $this->createForm(InterestPointFormType::class, $point);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('mainImageUpload')->getData();
            if ($imageFile) {
                $this->deletePlaceImage($point->getMainImage(), $point, false);
                $point->setMainImage($this->handlePlaceImageUpload($imageFile, $point, false));
            }

            $point->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();

            $this->addFlash('success', 'Le lieu a été modifié avec succès.');
            return $this->redirectToRoute('admin_maps_editor', ['id' => $point->getMap()->getId()]);
        }

        return $this->render('admin/maps/point_form.html.twig', [
            'form' => $form->createView(),
            'point' => $point,
            'title' => 'Modifier le Lieu',
        ]);
    }

    /**
     * Delete an interest point
     */
    #[Route('/points/{id}/delete', name: 'admin_maps_points_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function deletePoint(Request $request, InterestPoint $point): Response
    {
        $mapId = $point->getMap()->getId();

        if ($this->isCsrfTokenValid('delete' . $point->getId(), $request->request->get('_token'))) {
            // Delete main image
            $this->deletePlaceImage($point->getMainImage(), $point, false);
            
            // Delete gallery images
            $gallery = $point->getGalleryNormalized();
            foreach ($gallery as $item) {
                $this->deletePlaceImage($item['filename'], $point, true);
            }
            
            // Try to remove the place folder if empty
            $placeFolder = $this->getParameter('kernel.project_dir') . '/public/images/places/' . $point->getSlug();
            $this->removeEmptyDirectory($placeFolder . '/gallery');
            $this->removeEmptyDirectory($placeFolder);
            
            $this->entityManager->remove($point);
            $this->entityManager->flush();

            $this->addFlash('success', 'Le lieu a été supprimé avec succès.');
        }

        return $this->redirectToRoute('admin_maps_editor', ['id' => $mapId]);
    }

    /**
     * API: Update gallery image name
     */
    #[Route('/api/gallery/rename', name: 'admin_maps_api_gallery_rename', methods: ['POST'])]
    public function apiRenameGalleryImage(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pointId = $data['point_id'] ?? null;
        $filename = $data['filename'] ?? null;
        $newName = $data['new_name'] ?? null;

        if (!$pointId || !$filename || !$newName) {
            return $this->json(['success' => false, 'message' => 'Paramètres manquants']);
        }

        $point = $this->pointRepository->find($pointId);
        if (!$point) {
            return $this->json(['success' => false, 'message' => 'Lieu non trouvé']);
        }

        try {
            $point->updateGalleryImageName($filename, $newName);
            $point->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Nom de l\'image mis à jour',
            ]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * API: Delete gallery image
     */
    #[Route('/api/gallery/delete', name: 'admin_maps_api_gallery_delete', methods: ['POST'])]
    public function apiDeleteGalleryImage(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pointId = $data['point_id'] ?? null;
        $filename = $data['filename'] ?? null;

        if (!$pointId || !$filename) {
            return $this->json(['success' => false, 'message' => 'Paramètres manquants']);
        }

        $point = $this->pointRepository->find($pointId);
        if (!$point) {
            return $this->json(['success' => false, 'message' => 'Lieu non trouvé']);
        }

        try {
            $point->removeGalleryImage($filename);
            $this->deletePlaceImage($filename, $point, true);
            $point->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Image supprimée',
            ]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * API: Upload gallery images (for modal upload)
     */
    #[Route('/api/gallery/upload', name: 'admin_maps_api_gallery_upload', methods: ['POST'])]
    public function apiUploadGalleryImages(Request $request): JsonResponse
    {
        $pointId = $request->request->get('point_id');
        $files = $request->files->get('gallery_images');

        if (!$pointId) {
            return $this->json(['success' => false, 'message' => 'ID du lieu manquant']);
        }

        $point = $this->pointRepository->find($pointId);
        if (!$point) {
            return $this->json(['success' => false, 'message' => 'Lieu non trouvé']);
        }

        // Ensure files is an array
        if (!$files) {
            return $this->json(['success' => false, 'message' => 'Aucun fichier sélectionné']);
        }
        
        if (!is_array($files)) {
            $files = [$files];
        }
        
        if (count($files) === 0) {
            return $this->json(['success' => false, 'message' => 'Aucun fichier sélectionné']);
        }

        $uploaded = [];
        $errors = [];
        $maxSize = 5 * 1024 * 1024; // 5MB
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        foreach ($files as $file) {
            // Skip if file is not valid
            if (!$file instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
                continue;
            }
            
            if (!$file->isValid()) {
                $errors[] = 'Erreur de téléchargement: ' . $file->getErrorMessage();
                continue;
            }
            
            $originalName = $file->getClientOriginalName();
            
            // Validate file size
            if ($file->getSize() > $maxSize) {
                $errors[] = "\"$originalName\" dépasse la taille maximale de 5 Mo";
                continue;
            }
            
            // Validate mime type
            $mimeType = $file->getClientMimeType();
            if (!in_array($mimeType, $allowedMimes)) {
                $errors[] = "\"$originalName\" n'est pas un format d'image valide";
                continue;
            }

            try {
                $filename = $this->handlePlaceImageUpload($file, $point, true);
                $point->addGalleryImage($filename);
                $uploaded[] = [
                    'filename' => $filename,
                    'name' => $point->getGalleryNormalized()[count($point->getGalleryNormalized()) - 1]['name'],
                    'src' => '/images/places/gallery/' . $filename,
                ];
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (count($uploaded) > 0) {
            $point->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();
        }

        $message = count($uploaded) . ' image(s) ajoutée(s)';
        if (count($errors) > 0) {
            $message .= '. Erreurs: ' . implode('; ', $errors);
        }

        return $this->json([
            'success' => count($uploaded) > 0,
            'message' => $message,
            'uploaded' => $uploaded,
            'errors' => $errors,
            'total_uploaded' => count($uploaded),
            'total_errors' => count($errors),
        ]);
    }

    // ===== API Endpoints for Interactive Editor =====

    /**
     * API: Save points from interactive editor
     */
    #[Route('/api/save-points', name: 'admin_maps_api_save_points', methods: ['POST'])]
    public function apiSavePoints(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $mapId = $data['map_id'] ?? null;
        $points = $data['points'] ?? [];

        if (!$mapId) {
            return $this->json(['success' => false, 'message' => 'ID de carte manquant']);
        }

        $map = $this->mapRepository->find($mapId);
        if (!$map) {
            return $this->json(['success' => false, 'message' => 'Carte non trouvée']);
        }

        try {
            $savedPoints = [];

            foreach ($points as $pointData) {
                // Check if point has a database ID (existing point)
                if (!empty($pointData['database_id'])) {
                    $point = $this->pointRepository->find($pointData['database_id']);
                    if (!$point) {
                        continue;
                    }
                } else {
                    $point = new InterestPoint();
                    $point->setMap($map);
                }

                $point->setName($pointData['name']);
                $point->setDescription($pointData['description'] ?? '');
                $point->setXCoordinate((string) $pointData['x']);
                $point->setYCoordinate((string) $pointData['y']);

                // Set type if provided
                if (!empty($pointData['type'])) {
                    $type = $this->typeRepository->findByName($pointData['type']);
                    $point->setType($type);
                }

                if (!$point->getId()) {
                    $this->entityManager->persist($point);
                } else {
                    $point->setUpdatedAt(new \DateTimeImmutable());
                }

                $this->entityManager->flush();

                $savedPoints[] = [
                    'local_id' => $pointData['id'] ?? null,
                    'database_id' => $point->getId(),
                ];
            }

            return $this->json([
                'success' => true,
                'message' => count($points) . ' points sauvegardés',
                'saved_points' => $savedPoints,
            ]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * API: Delete a point
     */
    #[Route('/api/delete-point', name: 'admin_maps_api_delete_point', methods: ['POST'])]
    public function apiDeletePoint(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pointId = $data['database_id'] ?? null;

        if (!$pointId) {
            return $this->json(['success' => false, 'message' => 'ID de point manquant']);
        }

        $point = $this->pointRepository->find($pointId);
        if (!$point) {
            return $this->json(['success' => false, 'message' => 'Point non trouvé']);
        }

        try {
            $this->entityManager->remove($point);
            $this->entityManager->flush();

            return $this->json(['success' => true, 'message' => 'Point supprimé']);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * API: Update a single point
     */
    #[Route('/api/update-point', name: 'admin_maps_api_update_point', methods: ['POST'])]
    public function apiUpdatePoint(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pointId = $data['database_id'] ?? null;

        if (!$pointId) {
            return $this->json(['success' => false, 'message' => 'ID de point manquant']);
        }

        $point = $this->pointRepository->find($pointId);
        if (!$point) {
            return $this->json(['success' => false, 'message' => 'Point non trouvé']);
        }

        try {
            $point->setName($data['name'] ?? $point->getName());
            $point->setDescription($data['description'] ?? $point->getDescription());
            
            if (isset($data['x'])) {
                $point->setXCoordinate((string) $data['x']);
            }
            if (isset($data['y'])) {
                $point->setYCoordinate((string) $data['y']);
            }

            if (!empty($data['type'])) {
                $type = $this->typeRepository->findByName($data['type']);
                $point->setType($type);
            }

            $point->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();

            return $this->json(['success' => true, 'message' => 'Point mis à jour']);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * API: Clear all points from a map
     */
    #[Route('/api/clear-points', name: 'admin_maps_api_clear_points', methods: ['POST'])]
    public function apiClearPoints(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $mapId = $data['map_id'] ?? null;

        if (!$mapId) {
            return $this->json(['success' => false, 'message' => 'ID de carte manquant']);
        }

        $map = $this->mapRepository->find($mapId);
        if (!$map) {
            return $this->json(['success' => false, 'message' => 'Carte non trouvée']);
        }

        try {
            $deleted = $this->pointRepository->deleteAllFromMap($map);

            return $this->json([
                'success' => true,
                'message' => $deleted . ' points supprimés',
            ]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    // ===== Helper Methods =====

    /**
     * Handle image upload for maps
     */
    private function handleImageUpload($file, string $folder): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/images/' . $folder;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        try {
            $file->move($uploadDir, $newFilename);
        } catch (FileException $e) {
            throw new \Exception('Erreur lors du téléchargement de l\'image');
        }

        return $newFilename;
    }

    /**
     * Handle image upload for a place (main or gallery)
     */
    private function handlePlaceImageUpload($file, InterestPoint $point, bool $isGallery = false): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/images/places';
        
        if ($isGallery) {
            $uploadDir .= '/gallery';
        }
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        try {
            $file->move($uploadDir, $newFilename);
        } catch (FileException $e) {
            throw new \Exception('Erreur lors du téléchargement de "' . $file->getClientOriginalName() . '"');
        }

        return $newFilename;
    }

    /**
     * Delete old image from places folder
     */
    private function deletePlaceImage(?string $filename, InterestPoint $point, bool $isGallery = false): void
    {
        if (!$filename) {
            return;
        }

        $basePath = $this->getParameter('kernel.project_dir') . '/public/images/places';
        
        if ($isGallery) {
            $filePath = $basePath . '/gallery/' . $filename;
        } else {
            $filePath = $basePath . '/' . $filename;
        }
        
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    private function deleteOldImage(?string $filename, string $folder): void
    {
        if (!$filename) {
            return;
        }

        $filePath = $this->getParameter('kernel.project_dir') . '/public/images/' . $folder . '/' . $filename;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    /**
     * Remove an empty directory
     */
    private function removeEmptyDirectory(string $path): void
    {
        if (is_dir($path) && count(scandir($path)) === 2) { // Only . and ..
            rmdir($path);
        }
    }
}
