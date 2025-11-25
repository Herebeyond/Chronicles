<?php

namespace App\Controller;

use App\Entity\Idea;
use App\Entity\IdeaCategory;
use App\Form\IdeaType;
use App\Repository\IdeaRepository;
use App\Repository\IdeaCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/ideas')]
class IdeasController extends AbstractController
{
    /**
     * Check if request is AJAX by examining headers
     */
    private function isAjaxRequest(Request $request): bool
    {
        return $request->isXmlHttpRequest() 
            || $request->headers->get('X-Requested-With') === 'XMLHttpRequest'
            || $request->query->get('ajax') === '1';
    }

    #[Route('/', name: 'ideas_index')]
    public function index(
        Request $request,
        IdeaRepository $ideaRepository,
        IdeaCategoryRepository $categoryRepository
    ): Response {
        // Get filter parameters
        $search = $request->query->get('search');
        $category = $request->query->get('category');
        $certaintyLevel = $request->query->get('certainty');
        $status = $request->query->get('status');
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 10;

        // Get filtered ideas with pagination
        $ideas = $ideaRepository->findWithFilters(
            $search,
            $category,
            $certaintyLevel,
            $status,
            $page,
            $limit
        );

        // Get total count for pagination
        $totalCount = $ideaRepository->countWithFilters(
            $search,
            $category,
            $certaintyLevel,
            $status
        );
        $totalPages = max(1, (int) ceil($totalCount / $limit));

        // Get statistics
        $stats = $ideaRepository->getStatistics();

        // Get all categories for filter dropdown
        $categories = $categoryRepository->findAllOrdered();

        // Get all tags
        $allTags = $ideaRepository->getAllTags();

        return $this->render('ideas/index.html.twig', [
            'ideas' => $ideas,
            'categories' => $categories,
            'stats' => $stats,
            'allTags' => $allTags,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalCount' => $totalCount,
            'filters' => [
                'search' => $search,
                'category' => $category,
                'certainty' => $certaintyLevel,
                'status' => $status,
            ],
        ]);
    }

    #[Route('/create', name: 'ideas_create', methods: ['GET', 'POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        IdeaCategoryRepository $categoryRepository
    ): Response|JsonResponse {
        // Initialize default categories if needed
        $categoryRepository->initializeDefaultCategories();

        $idea = new Idea();
        $form = $this->createForm(IdeaType::class, $idea);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle tags from string input
            $tagsString = $form->get('tagsString')->getData();
            $idea->setTagsFromString($tagsString);

            $em->persist($idea);
            $em->flush();

            // Check if AJAX request
            if ($this->isAjaxRequest($request)) {
                return new JsonResponse([
                    'success' => true,
                    'message' => 'Idea created successfully!',
                    'idea_id' => $idea->getId()
                ]);
            }

            $this->addFlash('success', 'Idea created successfully!');
            return $this->redirectToRoute('ideas_index');
        }

        // If form was submitted but has errors, and it's AJAX, return errors as JSON
        if ($form->isSubmitted() && !$form->isValid() && $this->isAjaxRequest($request)) {
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $errors[] = $error->getMessage();
            }
            return new JsonResponse([
                'success' => false,
                'message' => 'Form validation failed',
                'errors' => $errors
            ], 400);
        }

        // Check if AJAX request for form HTML
        if ($this->isAjaxRequest($request) && $request->isMethod('GET')) {
            return $this->render('ideas/_form_content.html.twig', [
                'form' => $form,
                'idea' => $idea,
                'isEdit' => false,
            ]);
        }

        return $this->render('ideas/form.html.twig', [
            'form' => $form,
            'idea' => $idea,
            'isEdit' => false,
        ]);
    }

    #[Route('/{id}/edit', name: 'ideas_edit', methods: ['GET', 'POST'])]
    public function edit(
        Idea $idea,
        Request $request,
        EntityManagerInterface $em
    ): Response|JsonResponse {
        $form = $this->createForm(IdeaType::class, $idea);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle tags from string input
            $tagsString = $form->get('tagsString')->getData();
            $idea->setTagsFromString($tagsString);

            $idea->updateTimestamp();
            $em->flush();

            // Check if AJAX request
            if ($this->isAjaxRequest($request)) {
                return new JsonResponse([
                    'success' => true,
                    'message' => 'Idea updated successfully!',
                    'idea_id' => $idea->getId()
                ]);
            }

            $this->addFlash('success', 'Idea updated successfully!');
            return $this->redirectToRoute('ideas_index');
        }

        // If form was submitted but has errors, and it's AJAX, return errors as JSON
        if ($form->isSubmitted() && !$form->isValid() && $this->isAjaxRequest($request)) {
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $errors[] = $error->getMessage();
            }
            return new JsonResponse([
                'success' => false,
                'message' => 'Form validation failed',
                'errors' => $errors
            ], 400);
        }

        // Check if AJAX request for form HTML
        if ($this->isAjaxRequest($request) && $request->isMethod('GET')) {
            return $this->render('ideas/_form_content.html.twig', [
                'form' => $form,
                'idea' => $idea,
                'isEdit' => true,
            ]);
        }

        return $this->render('ideas/form.html.twig', [
            'form' => $form,
            'idea' => $idea,
            'isEdit' => true,
        ]);
    }

    #[Route('/{id}/delete', name: 'ideas_delete', methods: ['POST'])]
    public function delete(
        Idea $idea,
        EntityManagerInterface $em
    ): Response {
        $em->remove($idea);
        $em->flush();

        $this->addFlash('success', 'Idea deleted successfully!');
        return $this->redirectToRoute('ideas_index');
    }

    #[Route('/{id}/duplicate', name: 'ideas_duplicate', methods: ['POST'])]
    public function duplicate(
        Idea $idea,
        EntityManagerInterface $em
    ): Response {
        $newIdea = new Idea();
        $newIdea->setTitle($idea->getTitle() . ' (Copy)');
        $newIdea->setContent($idea->getContent());
        $newIdea->setCategory($idea->getCategory());
        $newIdea->setCertaintyLevel($idea->getCertaintyLevel());
        $newIdea->setStatus($idea->getStatus());
        $newIdea->setTags($idea->getTags());
        $newIdea->setComments($idea->getComments());
        $newIdea->setInspirationSource($idea->getInspirationSource());
        $newIdea->setPriority($idea->getPriority());

        $em->persist($newIdea);
        $em->flush();

        $this->addFlash('success', 'Idea duplicated successfully!');
        return $this->redirectToRoute('ideas_index');
    }

    #[Route('/quick-add', name: 'ideas_quick_add', methods: ['POST'])]
    public function quickAdd(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        try {
            $idea = new Idea();
            $idea->setTitle($request->request->get('title'));
            $idea->setContent($request->request->get('content'));
            $idea->setCategory($request->request->get('category', 'Other'));
            $idea->setCertaintyLevel($request->request->get('certainty', 'Idea'));
            $idea->setStatus('Draft');

            // Handle tags
            $tagsString = $request->request->get('tags');
            $idea->setTagsFromString($tagsString);

            $em->persist($idea);
            $em->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Idea added successfully!',
                'idea_id' => $idea->getId()
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    #[Route('/bulk-import', name: 'ideas_bulk_import', methods: ['POST'])]
    public function bulkImport(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        try {
            $ideasText = $request->request->get('ideasText');
            $defaultCategory = $request->request->get('defaultCategory', 'Other');
            $defaultCertainty = $request->request->get('defaultCertainty', 'Idea');

            // Split ideas by separator
            $ideaBlocks = preg_split('/\n---\n|\r\n---\r\n/', $ideasText);
            $imported = 0;
            $errors = [];

            foreach ($ideaBlocks as $block) {
                $block = trim($block);
                if (empty($block)) {
                    continue;
                }

                try {
                    // Parse idea block
                    $title = '';
                    $content = '';
                    $tags = null;

                    // Extract title
                    if (preg_match('/Title:\s*(.+?)(?:\n|\r\n)/i', $block, $matches)) {
                        $title = trim($matches[1]);
                    }

                    // Extract content
                    if (preg_match('/Content:\s*(.+?)(?=Tags:|$)/is', $block, $matches)) {
                        $content = trim($matches[1]);
                    }

                    // Extract tags
                    if (preg_match('/Tags:\s*(.+?)$/is', $block, $matches)) {
                        $tagsString = trim($matches[1]);
                        $tags = array_map('trim', explode(',', $tagsString));
                        $tags = array_filter($tags);
                    }

                    if (empty($title) || empty($content)) {
                        $errors[] = 'Skipped block: missing title or content';
                        continue;
                    }

                    $idea = new Idea();
                    $idea->setTitle($title);
                    $idea->setContent($content);
                    $idea->setCategory($defaultCategory);
                    $idea->setCertaintyLevel($defaultCertainty);
                    $idea->setStatus('Draft');
                    $idea->setTags($tags);

                    $em->persist($idea);
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = 'Error processing block: ' . $e->getMessage();
                }
            }

            $em->flush();

            return new JsonResponse([
                'success' => true,
                'imported_count' => $imported,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    #[Route('/export', name: 'ideas_export')]
    public function export(IdeaRepository $ideaRepository): Response
    {
        $ideas = $ideaRepository->findAllForExport();

        $content = "Universe Ideas Export\n";
        $content .= "Generated: " . date('Y-m-d H:i:s') . "\n";
        $content .= "Total Ideas: " . count($ideas) . "\n";
        $content .= str_repeat('=', 80) . "\n\n";

        foreach ($ideas as $idea) {
            $content .= "Title: " . $idea->getTitle() . "\n";
            $content .= "Category: " . str_replace('_', ' ', $idea->getCategory()) . "\n";
            $content .= "Certainty: " . str_replace('_', ' ', $idea->getCertaintyLevel()) . "\n";
            $content .= "Status: " . ($idea->getStatus() ?? 'N/A') . "\n";
            
            if ($idea->getParentIdea()) {
                $content .= "Parent Idea: " . $idea->getParentIdea()->getTitle() . "\n";
            }
            
            if ($idea->getTags()) {
                $content .= "Tags: " . implode(', ', $idea->getTags()) . "\n";
            }
            
            $content .= "\nContent:\n" . $idea->getContent() . "\n";
            
            if ($idea->getInspirationSource()) {
                $content .= "\nInspiration Source: " . $idea->getInspirationSource() . "\n";
            }
            
            if ($idea->getComments()) {
                $content .= "\nComments: " . $idea->getComments() . "\n";
            }
            
            $content .= "\nCreated: " . $idea->getCreatedAt()->format('Y-m-d H:i:s') . "\n";
            
            if ($idea->getUpdatedAt()) {
                $content .= "Updated: " . $idea->getUpdatedAt()->format('Y-m-d H:i:s') . "\n";
            }
            
            $content .= "\n" . str_repeat('-', 80) . "\n\n";
        }

        $response = new Response($content);
        $response->headers->set('Content-Type', 'text/plain; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="ideas_export_' . date('Y-m-d') . '.txt"');

        return $response;
    }

    #[Route('/categories', name: 'ideas_categories', methods: ['GET'])]
    public function getCategories(IdeaCategoryRepository $categoryRepository): JsonResponse
    {
        $categories = $categoryRepository->findAllOrdered();
        $data = array_map(fn($cat) => [
            'id' => $cat->getId(),
            'name' => $cat->getName(),
            'isDefault' => $cat->isDefault()
        ], $categories);

        return new JsonResponse(['success' => true, 'categories' => $data]);
    }

    #[Route('/categories/add', name: 'ideas_category_add', methods: ['POST'])]
    public function addCategory(
        Request $request,
        EntityManagerInterface $em,
        IdeaCategoryRepository $categoryRepository
    ): JsonResponse {
        try {
            $name = trim($request->request->get('name', ''));
            
            if (empty($name)) {
                throw new \Exception('Category name is required');
            }

            // Check if category already exists
            $existing = $categoryRepository->findByName($name);
            if ($existing) {
                throw new \Exception('Category already exists');
            }

            $category = new IdeaCategory();
            $category->setName($name);
            $category->setIsDefault(false);

            $em->persist($category);
            $em->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Category added successfully',
                'category' => [
                    'id' => $category->getId(),
                    'name' => $category->getName(),
                    'isDefault' => $category->isDefault()
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    #[Route('/categories/{id}/delete', name: 'ideas_category_delete', methods: ['POST'])]
    public function deleteCategory(
        IdeaCategory $category,
        EntityManagerInterface $em,
        IdeaRepository $ideaRepository
    ): JsonResponse {
        try {
            // Check if category is used by any ideas
            $usageCount = $ideaRepository->count(['category' => $category->getName()]);
            
            if ($usageCount > 0) {
                return new JsonResponse([
                    'success' => false,
                    'message' => "Cannot delete category. It is used by {$usageCount} idea(s)."
                ], 400);
            }

            // Don't allow deleting default categories
            if ($category->isDefault()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Cannot delete default category'
                ], 400);
            }

            $em->remove($category);
            $em->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    #[Route('/tags', name: 'ideas_tags', methods: ['GET'])]
    public function getTags(IdeaRepository $ideaRepository): JsonResponse
    {
        $tags = $ideaRepository->getAllTags();
        return new JsonResponse(['success' => true, 'tags' => $tags]);
    }
}
