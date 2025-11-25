<?php

namespace App\Repository;

use App\Entity\Idea;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Idea>
 */
class IdeaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Idea::class);
    }

    /**
     * Find ideas with optional filters and pagination
     */
    public function findWithFilters(
        ?string $search = null,
        ?string $category = null,
        ?string $certaintyLevel = null,
        ?string $status = null,
        int $page = 1,
        int $limit = 10
    ): array {
        $qb = $this->createQueryBuilder('i')
            ->leftJoin('i.parentIdea', 'p')
            ->leftJoin('i.children', 'c')
            ->addSelect('p')
            ->addSelect('c');

        // Apply search filter
        if ($search) {
            $searchTerms = preg_split('/\s+/', trim($search));
            $searchTerms = array_filter($searchTerms);
            
            if (!empty($searchTerms)) {
                $orConditions = $qb->expr()->orX();
                foreach ($searchTerms as $index => $term) {
                    $orConditions->add($qb->expr()->like('i.title', ":search{$index}"));
                    $orConditions->add($qb->expr()->like('i.content', ":search{$index}"));
                    $orConditions->add($qb->expr()->like('i.comments', ":search{$index}"));
                    $qb->setParameter("search{$index}", '%' . $term . '%');
                }
                $qb->andWhere($orConditions);
            }
        } else {
            // When not searching, only show parent ideas (for hierarchy display)
            $qb->andWhere('i.parentIdea IS NULL');
        }

        // Apply category filter
        if ($category) {
            $qb->andWhere('i.category = :category')
                ->setParameter('category', $category);
        }

        // Apply certainty level filter
        if ($certaintyLevel) {
            $qb->andWhere('i.certaintyLevel = :certaintyLevel')
                ->setParameter('certaintyLevel', $certaintyLevel);
        }

        // Apply status filter
        if ($status) {
            $qb->andWhere('i.status = :status')
                ->setParameter('status', $status);
        }

        // Order by creation date (newest first)
        $qb->orderBy('i.createdAt', 'DESC');

        // Pagination
        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    /**
     * Count total ideas with filters
     */
    public function countWithFilters(
        ?string $search = null,
        ?string $category = null,
        ?string $certaintyLevel = null,
        ?string $status = null
    ): int {
        $qb = $this->createQueryBuilder('i')
            ->select('COUNT(i.id)');

        // Apply search filter
        if ($search) {
            $searchTerms = preg_split('/\s+/', trim($search));
            $searchTerms = array_filter($searchTerms);
            
            if (!empty($searchTerms)) {
                $orConditions = $qb->expr()->orX();
                foreach ($searchTerms as $index => $term) {
                    $orConditions->add($qb->expr()->like('i.title', ":search{$index}"));
                    $orConditions->add($qb->expr()->like('i.content', ":search{$index}"));
                    $orConditions->add($qb->expr()->like('i.comments', ":search{$index}"));
                    $qb->setParameter("search{$index}", '%' . $term . '%');
                }
                $qb->andWhere($orConditions);
            }
        } else {
            // When not searching, only count parent ideas
            $qb->andWhere('i.parentIdea IS NULL');
        }

        // Apply category filter
        if ($category) {
            $qb->andWhere('i.category = :category')
                ->setParameter('category', $category);
        }

        // Apply certainty level filter
        if ($certaintyLevel) {
            $qb->andWhere('i.certaintyLevel = :certaintyLevel')
                ->setParameter('certaintyLevel', $certaintyLevel);
        }

        // Apply status filter
        if ($status) {
            $qb->andWhere('i.status = :status')
                ->setParameter('status', $status);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Get statistics for dashboard
     */
    public function getStatistics(): array
    {
        $total = $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $canon = $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->where('i.certaintyLevel = :canon')
            ->setParameter('canon', 'Canon')
            ->getQuery()
            ->getSingleScalarResult();

        $developing = $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->where('i.certaintyLevel = :developing')
            ->setParameter('developing', 'Developing')
            ->getQuery()
            ->getSingleScalarResult();

        $categories = $this->createQueryBuilder('i')
            ->select('COUNT(DISTINCT i.category)')
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'total' => (int) $total,
            'canon' => (int) $canon,
            'developing' => (int) $developing,
            'categories' => (int) $categories,
        ];
    }

    /**
     * Get all unique tags from all ideas
     */
    public function getAllTags(): array
    {
        $ideas = $this->createQueryBuilder('i')
            ->select('i.tags')
            ->where('i.tags IS NOT NULL')
            ->getQuery()
            ->getResult();

        $allTags = [];
        foreach ($ideas as $idea) {
            if (is_array($idea['tags'])) {
                $allTags = array_merge($allTags, $idea['tags']);
            }
        }

        $allTags = array_unique($allTags);
        sort($allTags);

        return array_values($allTags);
    }

    /**
     * Find all ideas for export
     */
    public function findAllForExport(): array
    {
        return $this->createQueryBuilder('i')
            ->leftJoin('i.parentIdea', 'p')
            ->addSelect('p')
            ->orderBy('i.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get parent ideas for dropdown selection
     */
    public function findParentOptions(?int $excludeId = null): array
    {
        $qb = $this->createQueryBuilder('i')
            ->orderBy('i.title', 'ASC');

        if ($excludeId) {
            $qb->where('i.id != :excludeId')
                ->setParameter('excludeId', $excludeId);
        }

        return $qb->getQuery()->getResult();
    }
}
